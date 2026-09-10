<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialCita extends Model
{
    protected $table = 'historial_citas';

    protected $guarded = ['id'];

    public const UPDATED_AT = null;

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
