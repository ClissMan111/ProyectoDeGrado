<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('rol', 30)->default('paciente');
            $table->boolean('estado')->default(true);
        });
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->unique()->constrained('users')->restrictOnDelete();
            $table->string('ci', 20)->unique();
            $table->string('nombres', 100);
            $table->string('apellidos', 120);
            $table->string('telefono', 20)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->timestamps();
        });
        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medico_id')->constrained('medicos')->restrictOnDelete();
            $table->unsignedTinyInteger('dia_semana'); // ISO: lunes=1, domingo=7.
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->unsignedSmallInteger('duracion_cita');
            $table->boolean('estado')->default(true);
            $table->timestamps();
            $table->index(['medico_id', 'dia_semana', 'estado']);
        });
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->restrictOnDelete();
            $table->foreignId('medico_id')->constrained('medicos')->restrictOnDelete();
            $table->foreignId('especialidad_id')->constrained('especialidades')->restrictOnDelete();
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->enum('estado', ['pendiente', 'confirmada', 'atendida', 'cancelada', 'no_asistio'])->default('pendiente');
            $table->string('motivo_cancelacion')->nullable();
            $table->timestamps();
            $table->index(['medico_id', 'fecha', 'estado']);
            $table->index(['paciente_id', 'fecha']);
        });
        Schema::create('historial_citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->constrained('citas')->restrictOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->restrictOnDelete();
            $table->string('accion', 50);
            foreach (['anterior', 'nueva'] as $suffix) {
                $table->date('fecha_'.$suffix)->nullable();
                $table->time('hora_inicio_'.$suffix)->nullable();
                $table->time('hora_fin_'.$suffix)->nullable();
            }
            $table->string('estado_anterior', 30)->nullable();
            $table->string('estado_nuevo', 30)->nullable();
            $table->string('motivo')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_citas');
        Schema::dropIfExists('citas');
        Schema::dropIfExists('horarios');
        Schema::dropIfExists('pacientes');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['rol', 'estado']));
    }
};
