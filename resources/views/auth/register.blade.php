@extends('layouts.public')
@section('title','Crear cuenta | Villa Israel')
@section('content')
<section class="register-page"><div class="container register-layout">
<aside><span class="section-tag light">NUEVA CUENTA</span><h1>Tu atención, en tus manos.</h1><p>Regístrate una sola vez para reservar y gestionar tus citas.</p><ol><li class="active"><span>1</span><div><strong>Datos personales</strong><small>Tu información básica</small></div></li><li><span>2</span><div><strong>Acceso personal</strong><small>Tu correo y contraseña</small></div></li></ol><div class="register-safe">✓ Una cuenta para todas tus citas.</div><x-care-photo image="orientacion-medica" class="register-photo" alt="Atención y orientación durante una consulta"/></aside>
<form class="register-card" action="{{ route('register.submit') }}" method="post">@csrf
<div class="form-title"><span>01</span><div><small>COMENCEMOS</small><h2>Crea tu cuenta de paciente</h2></div></div>
@include('partials.feedback')
<div class="form-grid">
@foreach(['nombres'=>'Nombres','apellidos'=>'Apellidos','ci'=>'Cédula de identidad','telefono'=>'Teléfono','fecha_nacimiento'=>'Fecha de nacimiento','email'=>'Correo electrónico'] as $key=>$label)
<label><span>{{ $label }} {{ in_array($key,['telefono','fecha_nacimiento'])?'(opcional)':'*' }}</span><input name="{{ $key }}" type="{{ ['email'=>'email','fecha_nacimiento'=>'date','telefono'=>'tel'][$key]??'text' }}" value="{{ old($key) }}" @required(!in_array($key,['telefono','fecha_nacimiento'])) @if($key==='fecha_nacimiento') max="{{ today()->format('Y-m-d') }}" @endif></label>
@endforeach
<label><span>Contraseña *</span><input name="password" type="password" minlength="8" required autocomplete="new-password" placeholder="Mínimo 8 caracteres"></label>
<label><span>Confirmar contraseña *</span><input name="password_confirmation" type="password" minlength="8" required autocomplete="new-password"></label>
</div><label class="checkbox legal-check"><input name="consentimiento" type="checkbox" value="1" required @checked(old('consentimiento'))><span>Acepto el uso de mis datos para gestionar mis citas médicas.</span></label>
<button class="button button-dark">Crear mi cuenta →</button><p>¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
</form></div></section>
@endsection
