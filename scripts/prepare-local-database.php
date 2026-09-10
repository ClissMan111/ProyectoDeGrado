<?php
// Creates only the configured database; never drops tables or replaces data.
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (!$app->environment('local') || config('database.default')!=='mysql') throw new RuntimeException('Solo MySQL local.');
$c=config('database.connections.mysql');
if (!preg_match('/^[a-zA-Z0-9_]+$/',$c['database']) || !in_array($c['host'],['127.0.0.1','localhost'])) throw new RuntimeException('Configuración no permitida.');
$pdo=new PDO('mysql:host='.$c['host'].';port='.$c['port'],$c['username'],$c['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$pdo->exec('CREATE DATABASE IF NOT EXISTS `'.$c['database'].'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
echo 'Base local preparada: '.$c['database'].PHP_EOL;
