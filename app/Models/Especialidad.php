<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Especialidad extends Model
{
    use HasFactory;

    protected $table = 'especialidades';

    protected $fillable = ['nombre', 'descripcion', 'estado'];

    protected $casts = ['estado' => 'boolean'];

    public function medicos(): BelongsToMany
    {
        return $this->belongsToMany(Medico::class, 'especialidad_medico')
            ->withTimestamps();
    }
}
