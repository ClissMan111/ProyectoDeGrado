@extends('layouts.dashboard')
@section('title','Mi cuenta')
@section('content')
<div class="dashboard-heading"><div><span class="section-tag">TU ESPACIO PERSONAL</span><h1>Cuenta y seguridad</h1><p>Mantén protegido el acceso a tus citas.</p></div></div>
<div class="detail-layout"><section class="surface"><h2>{{ $user->name }}</h2><dl class="detail-list"><div><dt>Correo electrónico</dt><dd>{{ $user->email }}</dd></div><div><dt>Perfil</dt><dd>{{ ucfirst($user->rol) }}</dd></div></dl><p class="form-hint">Para corregir tus datos personales, solicita ayuda a administración del centro.</p></section>
<form class="surface editor-form" method="post" action="{{ route('account.password') }}">@csrf @method('PUT')<h2>Cambiar contraseña</h2><label>Contraseña actual<input type="password" name="current_password" required autocomplete="current-password"></label><label>Nueva contraseña<input type="password" name="password" minlength="8" required autocomplete="new-password"></label><label>Confirmar nueva contraseña<input type="password" name="password_confirmation" minlength="8" required autocomplete="new-password"></label><button class="button button-dark">Actualizar contraseña</button></form></div>
@endsection
