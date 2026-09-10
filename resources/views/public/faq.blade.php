@extends('layouts.public')
@section('title', 'Preguntas frecuentes | Villa Israel')
@section('content')
<section class="page-hero compact"><div class="container page-hero-grid"><div><span class="section-tag light">AYUDA RÁPIDA</span><h1>Respuestas antes de reservar.</h1><p>Lo esencial para gestionar tu cita con tranquilidad.</p></div><div class="page-hero-index"><span>?</span><small>Centro de ayuda</small></div></div></section>
<section class="section faq-section"><div class="container faq-layout"><aside><span class="section-tag">PREGUNTAS FRECUENTES</span><h2>Si aún tienes dudas, podemos orientarte.</h2><a class="button button-dark" href="{{ route('contact') }}">Contactar al centro</a></aside><div class="accordion" data-accordion>
    @foreach ([
      ['¿Necesito una cuenta para reservar?','Sí. La cuenta protege tu información y permite consultar, cancelar o reprogramar tus propias citas.'],
      ['¿Puedo reservar desde mi celular?','Sí. Las pantallas están diseñadas para funcionar en celulares, tabletas y computadoras con un navegador actualizado.'],
      ['¿Qué sucede si no encuentro horarios?','Puedes cambiar la fecha o revisar otro profesional de la misma especialidad. Solo mostramos bloques realmente disponibles.'],
      ['¿Puedo cancelar o reprogramar?',config('citas.cambios_minutos')>0?'Sí, si la cita está pendiente o confirmada y faltan más de '.config('citas.cambios_minutos').' minutos para su inicio.':'Sí, si la cita está pendiente o confirmada y todavía no ha comenzado.'],
      ['¿Secretariado puede reservar por mí?','Sí. El administrador puede registrar una cita en nombre de un paciente que ya tenga una cuenta.'],
      ['¿Mi cita se confirma inmediatamente?','La reserva queda pendiente. El centro la confirma y puedes revisar su estado desde tu panel. Si la reprogramas, vuelve a quedar pendiente.'],
      ['¿El sistema guarda mi historia clínica?','No. Esta primera versión administra reservas e historial de citas, pero no diagnósticos, recetas ni tratamientos.']
    ] as $i => $faq)
    <article class="accordion-item {{ $i === 0 ? 'open' : '' }}"><button type="button" aria-expanded="{{ $i === 0 ? 'true' : 'false' }}"><span>{{ str_pad($i+1,2,'0',STR_PAD_LEFT) }}</span><strong>{{ $faq[0] }}</strong><i>+</i></button><div class="accordion-content"><p>{{ $faq[1] }}</p></div></article>
    @endforeach
</div></div></section>
@endsection
