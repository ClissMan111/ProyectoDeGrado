<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\IndisponibilidadMedico;
use App\Models\Medico;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IndisponibilidadService
{
    public function save(array $data, ?IndisponibilidadMedico $existing = null): IndisponibilidadMedico
    {
        return DB::transaction(function () use ($data, $existing) {
            // Same coordinating lock as reservations and weekly schedules.
            $medico = Medico::lockForUpdate()->findOrFail($data['medico_id']);
            if (! $medico->estado || ! $medico->usuario?->estado) {
                throw ValidationException::withMessages(['medico_id' => 'Selecciona un médico activo.']);
            }
            if ($existing) {
                $existing = IndisponibilidadMedico::lockForUpdate()->findOrFail($existing->id);
                if ($existing->medico_id !== $medico->id) {
                    throw ValidationException::withMessages(['medico_id' => 'Crea otro periodo para cambiar de médico.']);
                }
            }
            $start = Carbon::parse($data['inicio']);
            $end = Carbon::parse($data['fin']);
            if ($end->lte($start)) {
                throw ValidationException::withMessages(['fin' => 'La finalización debe ser posterior al inicio.']);
            }
            if ($data['estado']) {
                $affected = Cita::where('medico_id', $medico->id)->whereIn('estado', ['pendiente', 'confirmada'])
                    ->where(function ($q) use ($end) {
                        $q->whereDate('fecha', '<', $end->toDateString())->orWhere(fn ($q) => $q->whereDate('fecha', $end->toDateString())->where('hora_inicio', '<', $end->format('H:i:s')));
                    })->where(function ($q) use ($start) {
                        $q->whereDate('fecha', '>', $start->toDateString())->orWhere(fn ($q) => $q->whereDate('fecha', $start->toDateString())->where('hora_fin', '>', $start->format('H:i:s')));
                    })->lockForUpdate()->get();
                if ($affected->isNotEmpty()) {
                    throw ValidationException::withMessages(['inicio' => 'No se puede activar el bloqueo: existen '.$affected->count().' citas pendientes o confirmadas afectadas ('.$affected->pluck('id')->map(fn ($id) => '#'.$id)->join(', ').'). Gestiona estas citas en la agenda antes de guardar el bloqueo.']);
                }
            }
            $period = $existing ?? new IndisponibilidadMedico;
            $period->fill([...$data, 'inicio' => $start, 'fin' => $end])->save();

            return $period;
        }, 3);
    }
}
