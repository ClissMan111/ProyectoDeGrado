<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indisponibilidades_medico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medico_id')->constrained('medicos')->restrictOnDelete();
            $table->dateTime('inicio');
            $table->dateTime('fin');
            $table->string('motivo', 255)->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
            $table->index(['medico_id', 'estado', 'inicio', 'fin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indisponibilidades_medico');
    }
};
