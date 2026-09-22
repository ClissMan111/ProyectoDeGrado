<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const DAYS = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];

    public function up(): void
    {
        // Validate before any DDL: never truncate existing names or addresses.
        if (DB::table('users')->whereRaw('LENGTH(name) > 120 OR LENGTH(email) > 150')->get()->contains(fn ($u) => mb_strlen($u->name) > 120 || mb_strlen($u->email) > 150)) {
            throw new RuntimeException('Hay nombres de usuario o correos que exceden el diccionario de datos. Corrígelos antes de migrar.');
        }
        if (DB::table('users')->whereNotIn('rol', ['administrador', 'medico', 'paciente'])->exists() || DB::table('horarios')->whereNotIn('dia_semana', array_keys(self::DAYS))->exists()) {
            throw new RuntimeException('Existen roles o días de atención no reconocidos. Corrígelos antes de migrar.');
        }
        Schema::rename('users', 'usuarios');
        if (DB::getDriverName() === 'mysql') {
            // CHANGE also supports the MariaDB version bundled with XAMPP.
            DB::statement("ALTER TABLE usuarios CHANGE name nombre VARCHAR(120) NOT NULL, CHANGE email correo VARCHAR(150) NOT NULL, MODIFY rol ENUM('administrador','medico','paciente') NOT NULL DEFAULT 'paciente'");
            DB::statement('ALTER TABLE horarios MODIFY dia_semana VARCHAR(15) NOT NULL');
        } else {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->renameColumn('name', 'nombre');
                $table->renameColumn('email', 'correo');
            });
            Schema::table('horarios', function (Blueprint $table) {
                $table->dropIndex('horarios_medico_id_dia_semana_estado_index');
                $table->string('dia_documentado', 15)->default('Lunes');
            });
            foreach (self::DAYS as $number => $day) {
                DB::table('horarios')->where('dia_semana', $number)->update(['dia_documentado' => $day]);
            }
            Schema::table('horarios', fn (Blueprint $table) => $table->dropColumn('dia_semana'));
            Schema::table('horarios', fn (Blueprint $table) => $table->renameColumn('dia_documentado', 'dia_semana'));
            Schema::table('horarios', fn (Blueprint $table) => $table->index(['medico_id', 'dia_semana', 'estado']));
        }
        if (DB::getDriverName() === 'mysql') {
            foreach (self::DAYS as $number => $day) {
                DB::table('horarios')->where('dia_semana', (string) $number)->update(['dia_semana' => $day]);
            }
        }
    }

    public function down(): void
    {
        foreach (self::DAYS as $number => $day) {
            DB::table('horarios')->where('dia_semana', $day)->update(['dia_semana' => (string) $number]);
        }
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE horarios MODIFY dia_semana TINYINT UNSIGNED NOT NULL');
            DB::statement("ALTER TABLE usuarios CHANGE nombre name VARCHAR(255) NOT NULL, CHANGE correo email VARCHAR(255) NOT NULL, MODIFY rol VARCHAR(30) NOT NULL DEFAULT 'paciente'");
        } else {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->renameColumn('nombre', 'name');
                $table->renameColumn('correo', 'email');
            });
        }
        Schema::rename('usuarios', 'users');
    }
};
