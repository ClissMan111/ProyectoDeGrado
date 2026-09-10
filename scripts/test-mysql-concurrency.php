<?php
// Isolated integration check. Only the uniquely named database created here is removed.
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\{DB,Artisan};
use App\Models\{User,Paciente,Medico,Especialidad,Horario,Cita};
use App\Services\CitaService;
use Symfony\Component\Process\Process;

if (!$app->environment('local')) throw new RuntimeException('Solo entorno local.');
$name=$argv[2]??('proyecto_clis_qa_'.bin2hex(random_bytes(6)));
if (!preg_match('/^proyecto_clis_qa_[a-f0-9]{12}$/',$name)) throw new RuntimeException('Nombre de base no permitido.');
$c=config('database.connections.mysql');
if (!in_array($c['host'],['127.0.0.1','localhost'])) throw new RuntimeException('Solo servidor local.');
config(['database.default'=>'mysql','database.connections.mysql.database'=>$name]);DB::purge('mysql');
if (($argv[1]??'')==='worker') {
    $actor=User::findOrFail((int)$argv[3]);
    try {
        app(CitaService::class)->reserve($actor,['medico_id'=>(int)$argv[4],'especialidad_id'=>1,'fecha'=>$argv[5],'hora_inicio'=>'08:00']);
        echo 'RESERVED';
    } catch (Illuminate\Validation\ValidationException $e) { echo 'REJECTED'; }
    exit;
}
$pdo=new PDO('mysql:host='.$c['host'].';port='.$c['port'],$c['username'],$c['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$created=false;
try {
    $pdo->exec('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4');$created=true;
    Artisan::call('migrate',['--force'=>true]);
    $e=Especialidad::create(['nombre'=>'QA','estado'=>true]);
    $patients=[];$doctors=[];
    for($i=0;$i<2;$i++) {
        $u=User::create(['name'=>'QA Paciente '.$i,'email'=>'qa-p'.$i.'@example.test','password'=>'unused-test-password','rol'=>'paciente','estado'=>true]);
        Paciente::create(['usuario_id'=>$u->id,'ci'=>'P'.$i,'nombres'=>'QA','apellidos'=>'Paciente']);$patients[]=$u;
        $d=User::create(['name'=>'QA Médico '.$i,'email'=>'qa-m'.$i.'@example.test','password'=>'unused-test-password','rol'=>'medico','estado'=>true]);
        $m=Medico::create(['usuario_id'=>$d->id,'ci'=>'M'.$i,'nombres'=>'QA','apellidos'=>'Médico','estado'=>true]);$m->especialidades()->attach($e);$doctors[]=$m;
        foreach(range(1,7) as $day) Horario::create(['medico_id'=>$m->id,'dia_semana'=>$day,'hora_inicio'=>'08:00','hora_fin'=>'10:00','duracion_cita'=>30,'estado'=>true]);
    }
    foreach(['same_doctor','same_patient'] as $scenario) {
        $date=today()->addDays($scenario==='same_doctor'?1:2)->format('Y-m-d');
        DB::beginTransaction();
        if($scenario==='same_doctor') Medico::lockForUpdate()->find($doctors[0]->id);
        else Paciente::lockForUpdate()->find($patients[0]->paciente->id);
        $workers=[];
        for($i=0;$i<2;$i++) {
            $worker=new Process([PHP_BINARY,__FILE__,'worker',$name,(string)$patients[$scenario==='same_doctor'?$i:0]->id,(string)$doctors[$scenario==='same_doctor'?0:$i]->id,$date],base_path());
            $worker->setTimeout(25);$worker->start();$workers[]=$worker;
        }
        // Hold the coordinating row so both workers enter the race before release.
        usleep(1000000);DB::commit();
        $outputs=[];
        foreach($workers as $worker) {$worker->wait();if(!$worker->isSuccessful())throw new RuntimeException($worker->getErrorOutput().$worker->getOutput());$outputs[]=trim($worker->getOutput());}
        sort($outputs);
        if($outputs!==['REJECTED','RESERVED'] || Cita::whereDate('fecha',$date)->count()!==1) throw new RuntimeException('Concurrency failed: '.$scenario.' '.json_encode($outputs));
        echo 'PASS '.$scenario.': one reservation, one rejection'.PHP_EOL;
    }
} finally {
    while(DB::transactionLevel()>0)DB::rollBack();
    DB::disconnect('mysql');
    if($created)$pdo->exec('DROP DATABASE `'.$name.'`');
}
