@extends('layouts.public')
@section('title', 'Cómo reservar | Villa Israel')
@section('content')
<section class="page-hero compact"><div class="container page-hero-grid"><div><span class="section-tag light">GUÍA DE RESERVA</span><h1>Tu cita, paso a paso.</h1><p>Desde la búsqueda hasta el día de tu atención, siempre sabrás qué sigue.</p></div><div class="page-hero-index"><span>03</span><small>pasos sencillos</small></div></div></section>
<section class="section guide-section"><div class="container guide-layout">
    <aside class="guide-aside"><span>ANTES DE EMPEZAR</span><h2>Solo necesitas tus datos personales y un correo.</h2><p>Crear tu cuenta permite proteger tus citas y consultarlas desde cualquier dispositivo.</p><a class="button button-dark" href="{{ route('register') }}">Crear mi cuenta</a></aside>
    <div class="guide-steps">
        <article><span class="step-big">01</span><div><span class="step-label">BUSCA</span><h2>Selecciona una especialidad</h2><p>Elige el servicio que necesitas. Podrás ver a todos los médicos asociados, incluso cuando un profesional atienda en varias especialidades.</p></div></article>
        <article><span class="step-big">02</span><div><span class="step-label">ELIGE</span><h2>Compara los horarios disponibles</h2><p>Selecciona al profesional, la fecha y uno de los bloques libres. Los horarios ocupados no aparecen como disponibles.</p></div></article>
        <article><span class="step-big">03</span><div><span class="step-label">CONFIRMA</span><h2>Revisa y guarda tu cita</h2><p>Comprueba la especialidad, médico, fecha y hora. Después podrás verla, cancelarla o reprogramarla desde tu panel.</p></div></article>
    </div>
</div></section>
<section class="notice-band"><div class="container"><div><span>!</span><p><strong>Si no puedes asistir, libera el horario.</strong><br>Cancela o reprograma con anticipación para que otra persona pueda utilizarlo.</p></div><a href="{{ route('faq') }}">Ver preguntas frecuentes →</a></div></section>
@endsection
