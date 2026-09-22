<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('villa:demo', function () {
    if (! app()->environment('local')) {
        $this->error('Disponible únicamente en entorno local.');

        return 1;
    }
    $password = \Illuminate\Support\Str::password(16, symbols: false);
    \Illuminate\Support\Facades\DB::transaction(function () use ($password) {
        $specialties = [];
        foreach (['Medicina general' => 'Evaluación integral y seguimiento de tu salud.', 'Odontología' => 'Prevención y atención para cuidar tu salud bucal.', 'Fisioterapia' => 'Atención orientada a la movilidad y rehabilitación.', 'Laboratorio' => 'Apoyo diagnóstico según indicación profesional.'] as $name => $description) {
            $specialties[] = \App\Models\Especialidad::firstOrCreate(['nombre' => $name], ['descripcion' => $description, 'estado' => true]);
        }
        foreach (['administrador' => 'Administración Demo', 'medico' => 'Daniel Rojas Demo', 'paciente' => 'María Fernández Demo'] as $role => $name) {
            $email = $role.'@villaisrael.test';
            $user = \App\Models\User::firstOrCreate(['correo' => $email], ['name' => $name, 'rol' => $role, 'estado' => true, 'password' => \Illuminate\Support\Facades\Hash::make($password)]);
            if ($user->wasRecentlyCreated) {
                $this->line($email.' | '.$password);
            } else {
                $this->line($email.' | existente: conserva su contraseña');
            }
            if ($role === 'paciente') {
                \App\Models\Paciente::firstOrCreate(['usuario_id' => $user->id], ['ci' => 'DEMO-P-01', 'nombres' => 'María', 'apellidos' => 'Fernández Demo']);
            }
            if ($role === 'medico') {
                $medico = \App\Models\Medico::firstOrCreate(['usuario_id' => $user->id], ['ci' => 'DEMO-M-01', 'nombres' => 'Daniel', 'apellidos' => 'Rojas Demo', 'estado' => true]);
                $medico->especialidades()->syncWithoutDetaching([$specialties[0]->id, $specialties[2]->id]);
                foreach (range(1, 5) as $day) {
                    \App\Models\Horario::firstOrCreate(['medico_id' => $medico->id, 'dia_semana' => \App\Models\Horario::DIAS[$day], 'hora_inicio' => '08:00:00'], ['hora_fin' => '12:00:00', 'duracion_cita' => 30, 'estado' => true]);
                }
            }
        }
    });
    $this->info('Cuentas y horarios de demostración local. No representan la disponibilidad real del centro.');
})->purpose('Preparar cuentas y horarios de demostración local sin reemplazar registros existentes');

Artisan::command('villa:backup', function () {
    if (config('database.default') !== 'mysql') {
        $this->error('Este respaldo requiere MySQL.');

        return 1;
    }
    $c = config('database.connections.mysql');
    $directory = storage_path('app/backups');
    \Illuminate\Support\Facades\File::ensureDirectoryExists($directory);
    $path = $directory.'/citas-'.now()->format('Ymd-His').'-'.bin2hex(random_bytes(3)).'.sql';
    $binary = env('MYSQLDUMP_BINARY', PHP_OS_FAMILY === 'Windows' ? 'C:/xampp/mysql/bin/mysqldump.exe' : 'mysqldump');
    $process = new \Symfony\Component\Process\Process([$binary, '--single-transaction', '--quick', '--skip-lock-tables', '--host='.$c['host'], '--port='.$c['port'], '--user='.$c['username'], '--result-file='.$path, $c['database']], base_path(), ['MYSQL_PWD' => $c['password']]);
    $process->setTimeout(120);
    $process->run();
    if (! $process->isSuccessful()) {
        $this->error('No se pudo completar el respaldo. Revisa MySQL y MYSQLDUMP_BINARY.');

        return 1;
    }
    $this->info('Respaldo creado: '.$path);
})->purpose('Crear un respaldo SQL en storage/app/backups fuera de la carpeta pública');
