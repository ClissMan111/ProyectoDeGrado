<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['fecha' => 'date'];

    public const ESTADOS = ['pendiente' => 'Pendiente', 'confirmada' => 'Confirmada', 'atendida' => 'Atendida', 'cancelada' => 'Cancelada', 'no_asistio' => 'No asistió'];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function medico()
    {
        return $this->belongsTo(Medico::class);
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function historial()
    {
        return $this->hasMany(HistorialCita::class)->orderByDesc('id');
    }

    public function inicio(): Carbon
    {
        return Carbon::parse($this->fecha->format('Y-m-d').' '.$this->hora_inicio);
    }

    public function modificable(): bool
    {
        return in_array($this->estado, ['pendiente', 'confirmada']) && $this->inicio()->gt(now()->addMinutes(config('citas.cambios_minutos')));
    }

    public function scopeVisible($query, User $user)
    {
        return match ($user->rol) {
            'administrador' => $query,
            'medico' => $query->where('medico_id', $user->medico?->id ?? 0),
            default => $query->where('paciente_id', $user->paciente?->id ?? 0),
        };
    }
}
