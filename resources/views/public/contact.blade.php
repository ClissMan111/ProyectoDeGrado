@extends('layouts.public')
@section('title', 'Contacto | Villa Israel')
@section('content')
<section class="page-hero compact"><div class="container page-hero-grid"><div><span class="section-tag light">CONTACTO</span><h1>Estamos para orientarte.</h1><p>Encuentra nuestra ubicación y los canales de atención del centro.</p></div><div class="page-hero-index"><span>VI</span><small>Cochabamba</small></div></div></section>
<section class="section contact-section"><div class="container contact-grid">
    <div class="contact-cards"><article><span>01</span><small>UBICACIÓN</small><h2>Av. Panamericana</h2><p>Sobre Av. General Bartolomé Salomón<br>Cochabamba, Bolivia</p></article><article><span>02</span><small>HORARIO</small><h2>Lunes a viernes</h2><p>07:30 a 18:00<br>Consulta según disponibilidad</p></article><article><span>03</span><small>ORIENTACIÓN</small><h2>Atención presencial</h2><p>Acércate a Secretariado para consultas relacionadas con tus reservas.</p></article></div>
    <aside class="contact-form"><span class="section-tag">ORIENTACIÓN PARA TU VISITA</span><h2>Resolvamos tu siguiente paso.</h2><p>Para consultas sobre la atención del centro o correcciones de tus datos, acércate a Secretariado.</p><div class="contact-actions"><a class="button button-dark" href="{{ route('faq') }}">Consultar preguntas frecuentes →</a><a class="button ghost-button" href="{{ route('booking') }}">Buscar una cita →</a></div><p class="form-hint">Si ya tienes una reserva, puedes revisar su estado, cancelarla o reprogramarla desde tu cuenta.</p><a class="text-link dark" href="{{ route('panel') }}">Ir a mi panel <span>→</span></a></aside>
</div></section>
@endsection
