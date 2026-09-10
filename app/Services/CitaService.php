<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CitaService
{
    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['cita' => $message]);
    }

    public function slots(Medico $medico, int $especialidad, string $fecha, ?int $except = null): array
    {
        $day = Carbon::parse($fecha)->startOfDay();
        if ($day->lt(today()) || $day->gt(today()->addDays(config('citas.horizonte_dias')))) {
            return [];
        }
        if (! $medico->estado || ! $medico->usuario?->estado || ! $medico->especialidades()->whereKey($especialidad)->where('estado', true)->exists()) {
            return [];
        }
        $busy = Cita::where('medico_id', $medico->id)->whereDate('fecha', $fecha)->where('estado', '!=', 'cancelada')
            ->when($except, fn ($q) => $q->where('id', '!=', $except))
            ->when(DB::transactionLevel() > 0, fn ($q) => $q->lockForUpdate())->get();
        $slots = [];
        foreach ($medico->horarios()->where('estado', true)->where('dia_semana', $day->dayOfWeekIso)->orderBy('hora_inicio')->get() as $horario) {
            $start = Carbon::parse($fecha.' '.$horario->hora_inicio);
            $end = Carbon::parse($fecha.' '.$horario->hora_fin);
            while (($finish = $start->copy()->addMinutes($horario->duracion_cita))->lte($end)) {
                $from = $start->format('H:i:s');
                $to = $finish->format('H:i:s');
                if ($start->gt(now()->addMinutes(config('citas.anticipacion_minutos'))) && ! $busy->contains(fn ($c) => $c->hora_inicio < $to && $c->hora_fin > $from)) {
                    $slots[] = ['inicio' => $start->format('H:i'), 'fin' => $finish->format('H:i')];
                }
                $start = $finish;
            }
        }

        return $slots;
    }

    public function reserve(User $actor, array $data, ?Cita $existing = null): Cita
    {
        return DB::transaction(function () use ($actor, $data, $existing) {
            // Serialize every booking and schedule mutation on the doctor row.
            $medico = Medico::lockForUpdate()->findOrFail($existing?->medico_id ?? $data['medico_id']);
            $patientId = $existing?->paciente_id ?? ($actor->rol === 'administrador' ? $data['paciente_id'] : $actor->paciente?->id);
            $patient = Paciente::lockForUpdate()->findOrFail($patientId);
            abort_unless(in_array($actor->rol, ['paciente', 'administrador']), 403);
            if (! $patient->usuario->estado) {
                $this->fail('La cuenta del paciente está desactivada.');
            }
            $old = null;
            if ($existing) {
                $existing = Cita::lockForUpdate()->findOrFail($existing->id);
                abort_unless($actor->rol === 'paciente' && $patient->usuario_id === $actor->id, 403);
                if (! $existing->modificable()) {
                    $this->fail('Esta cita ya no admite reprogramación.');
                }
                $old = $existing->replicate();
            }
            $specialty = (int) ($existing?->especialidad_id ?? $data['especialidad_id']);
            $slot = collect($this->slots($medico, $specialty, $data['fecha'], $existing?->id))->firstWhere('inicio', $data['hora_inicio']);
            if (! $slot) {
                $this->fail('Ese horario ya no está disponible. Selecciona otro.');
            }
            if ($existing && $existing->fecha->format('Y-m-d') === $data['fecha'] && substr($existing->hora_inicio, 0, 5) === $slot['inicio']) {
                $this->fail('Selecciona una fecha u hora diferente.');
            }
            $overlap = Cita::where('paciente_id', $patient->id)->whereDate('fecha', $data['fecha'])->where('estado', '!=', 'cancelada')
                ->when($existing, fn ($q) => $q->where('id', '!=', $existing->id))->where('hora_inicio', '<', $slot['fin'].':00')->where('hora_fin', '>', $slot['inicio'].':00')->lockForUpdate()->get()->isNotEmpty();
            if ($overlap) {
                $this->fail('El paciente ya tiene una cita que coincide con ese horario.');
            }
            $cita = $existing ?? new Cita;
            $cita->fill(['paciente_id' => $patient->id, 'medico_id' => $medico->id, 'especialidad_id' => $specialty, 'fecha' => $data['fecha'], 'hora_inicio' => $slot['inicio'].':00', 'hora_fin' => $slot['fin'].':00', 'estado' => 'pendiente'])->save();
            $this->record($cita, $actor, $old ? 'reprogramacion' : 'reserva', $old);

            return $cita;
        }, 3);
    }

    public function changeState(User $actor, Cita $cita, string $state, ?string $reason = null): void
    {
        DB::transaction(function () use ($actor, $cita, $state, $reason) {
            Medico::lockForUpdate()->findOrFail($cita->medico_id);
            $cita = Cita::lockForUpdate()->findOrFail($cita->id);
            abort_unless(Cita::visible($actor)->whereKey($cita->id)->exists(), 403);
            if ($actor->rol === 'paciente') {
                abort_unless($state === 'cancelada', 403);
                if (! $cita->modificable()) {
                    $this->fail('Ya no puedes cancelar esta cita.');
                }
            } elseif ($actor->rol === 'medico') {
                abort_unless(in_array($state, ['atendida', 'no_asistio']), 403);
            }
            $allowed = ['pendiente' => ['confirmada', 'cancelada', 'no_asistio'], 'confirmada' => ['atendida', 'cancelada', 'no_asistio']];
            if (! in_array($state, $allowed[$cita->estado] ?? [])) {
                $this->fail('El cambio de estado no está permitido.');
            }
            if (in_array($state, ['atendida', 'no_asistio']) && $cita->inicio()->isFuture()) {
                $this->fail('Todavía no ha comenzado la cita.');
            }
            if ($state === 'confirmada' && $cita->inicio()->isPast()) {
                $this->fail('No puedes confirmar una cita cuyo horario ya pasó.');
            }
            $old = $cita->replicate();
            $cita->estado = $state;
            if ($state === 'cancelada') {
                $cita->motivo_cancelacion = $reason;
            }
            $cita->save();
            $this->record($cita, $actor, $state === 'cancelada' ? 'cancelacion' : 'estado', $old, $reason);
        }, 3);
    }

    private function record(Cita $cita, User $actor, string $action, ?Cita $old, ?string $reason = null): void
    {
        $cita->historial()->create(['usuario_id' => $actor->id, 'accion' => $action, 'fecha_anterior' => $old?->fecha, 'hora_inicio_anterior' => $old?->hora_inicio, 'hora_fin_anterior' => $old?->hora_fin, 'fecha_nueva' => $cita->fecha, 'hora_inicio_nueva' => $cita->hora_inicio, 'hora_fin_nueva' => $cita->hora_fin, 'estado_anterior' => $old?->estado, 'estado_nuevo' => $cita->estado, 'motivo' => $reason]);
    }
}
