<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\ReportController;
use App\Http\Middleware\ActiveRole;
use App\Models\Especialidad;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('public.home', ['especialidades' => Especialidad::where('estado', true)->orderBy('nombre')->get()]))->name('home');
Route::get('/especialidades', fn () => view('public.specialties', ['especialidades' => Especialidad::where('estado', true)->withCount(['medicos' => fn ($q) => $q->where('estado', true)->whereHas('usuario', fn ($q) => $q->where('estado', true))])->orderBy('nombre')->get()]))->name('specialties');
Route::view('/como-reservar', 'public.how-it-works')->name('how-it-works');
Route::view('/nosotros', 'public.about')->name('about');
Route::view('/contacto', 'public.contact')->name('contact');
Route::view('/preguntas-frecuentes', 'public.faq')->name('faq');
Route::middleware('guest')->group(function () {
    Route::view('/ingresar', 'auth.login')->name('login');
    Route::post('/ingresar', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login.submit');
    Route::view('/registro', 'auth.register')->name('register');
    Route::post('/registro', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.submit');
});
Route::middleware(['auth', ActiveRole::class])->group(function () {
    Route::get('/panel', fn () => redirect()->route(auth()->user()->panel()))->name('panel');
    foreach (['paciente' => 'patient', 'medico' => 'doctor', 'administracion' => 'admin'] as $path => $name) {
        Route::get('/panel/'.$path, [CitaController::class, 'dashboard'])->middleware(ActiveRole::class.':'.($path === 'administracion' ? 'administrador' : $path))->name('dashboard.'.$name);
    }
    Route::post('/salir', [AuthController::class, 'logout'])->name('logout');
    Route::get('/cuenta', [AuthController::class, 'profile'])->name('account');
    Route::put('/cuenta/password', [AuthController::class, 'password'])->middleware('throttle:6,1')->name('account.password');
    Route::get('/reservar', [CitaController::class, 'create'])->name('booking');
    Route::get('/disponibilidad', [CitaController::class, 'availability'])->middleware(ActiveRole::class.':paciente,administrador')->name('availability');
    Route::get('/citas', [CitaController::class, 'index'])->name('citas.index');
    Route::post('/citas', [CitaController::class, 'store'])->middleware('throttle:20,1')->name('citas.store');
    Route::get('/citas/{cita}', [CitaController::class, 'show'])->name('citas.show');
    Route::get('/citas/{cita}/reprogramar', [CitaController::class, 'create'])->name('citas.reschedule');
    Route::put('/citas/{cita}/reprogramar', [CitaController::class, 'store'])->name('citas.reschedule.save');
    Route::patch('/citas/{cita}/estado', [CitaController::class, 'state'])->name('citas.state');
    Route::prefix('administracion')->middleware(ActiveRole::class.':administrador')->group(function () {
        Route::get('/reportes', [ReportController::class, 'index'])->name('reports');
        Route::get('/{resource}', [AdminController::class, 'index'])->whereIn('resource', ['pacientes', 'medicos', 'especialidades', 'horarios'])->name('admin.index');
        Route::get('/{resource}/crear', [AdminController::class, 'edit'])->whereIn('resource', ['medicos', 'especialidades', 'horarios'])->name('admin.create');
        Route::post('/{resource}', [AdminController::class, 'save'])->whereIn('resource', ['medicos', 'especialidades', 'horarios'])->name('admin.store');
        Route::get('/{resource}/{id}/editar', [AdminController::class, 'edit'])->whereIn('resource', ['pacientes', 'medicos', 'especialidades', 'horarios'])->whereNumber('id')->name('admin.edit');
        Route::put('/{resource}/{id}', [AdminController::class, 'save'])->whereIn('resource', ['pacientes', 'medicos', 'especialidades', 'horarios'])->whereNumber('id')->name('admin.update');
    });
});
