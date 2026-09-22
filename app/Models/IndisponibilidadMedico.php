<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndisponibilidadMedico extends Model
{
    protected $table = 'indisponibilidades_medico';

    protected $fillable = ['medico_id', 'inicio', 'fin', 'motivo', 'estado'];

    protected $casts = ['inicio' => 'datetime', 'fin' => 'datetime', 'estado' => 'boolean'];

    public function medico()
    {
        return $this->belongsTo(Medico::class);
    }
}
