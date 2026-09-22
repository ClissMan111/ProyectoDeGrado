<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialCita extends Model
{
    protected $table = 'historial_citas';

    protected $guarded = ['id'];

    protected $casts = ['fecha_anterior' => 'date', 'fecha_nueva' => 'date'];

    public const UPDATED_AT = null;

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
