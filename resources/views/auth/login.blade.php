@extends('layouts.public')
@section('title','Ingresar | Villa Israel')
@section('content')
<section class="auth-page"><div class="container auth-grid">
<div class="auth-message"><span class="section-tag light">TU ESPACIO DE SALUD</span><h1>Vuelve a tus citas.</h1><p>Tu próxima atención, tu agenda y todo lo que necesitas, en un mismo lugar.</p><div class="auth-benefits"><span><x-icon name="calendar"/> Tus reservas</span><span><x-icon name="clock"/> Tu agenda</span><span><x-icon name="heart"/> Tu atención</span></div><x-care-photo image="consulta-digital" class="auth-photo" alt="Consulta de información de atención en una tableta"/><div class="auth-signature"><span class="auth-signature-line"></span><span>Centro de Salud <strong>Villa Israel</strong></span></div></div>
<div class="auth-card"><div class="auth-card-head"><span class="auth-icon">→</span><div><small>BIENVENIDO DE NUEVO</small><h2>Iniciar sesión</h2></div></div>
@include('partials.feedback')
<form action="{{ route('login.submit') }}" method="post">@csrf
<label><span>Correo electrónico</span><input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="nombre@correo.com"></label>
<label><span>Contraseña</span><span class="password-field"><input type="password" name="password" required autocomplete="current-password" placeholder="Tu contraseña"><button type="button" data-password-toggle aria-label="Mostrar contraseña">Ver</button></span></label>
<div class="form-options"><label class="checkbox"><input type="checkbox" name="remember" value="1"><span>Recordarme</span></label></div>
<button class="button button-dark button-wide">Ingresar a mi cuenta <span>→</span></button></form>
<p class="auth-register">¿Todavía no tienes cuenta? <a href="{{ route('register') }}">Regístrate como paciente</a></p><p class="form-hint">Si necesitas recuperar tu acceso, comunícate con administración del centro.</p>
</div></div></section>
@endsection
