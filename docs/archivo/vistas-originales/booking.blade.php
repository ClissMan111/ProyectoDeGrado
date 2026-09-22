@extends('layouts.public')
@section('title', 'Reservar cita | Villa Israel')
@section('content')
<section class="booking-page"><div class="container">
    <div class="booking-page-head"><div><span class="section-tag">NUEVA CITA</span><h1>Encuentra tu horario.</h1><p>Completa cada paso para consultar la disponibilidad.</p></div><a href="{{ route('how-it-works') }}">¿Cómo funciona? <span>↗</span></a></div>
    <div class="booking-workspace" data-booking-demo>
        <aside class="booking-progress"><ol><li class="active"><span>1</span><div><strong>Especialidad</strong><small>Elige la atención</small></div></li><li><span>2</span><div><strong>Profesional</strong><small>Selecciona un médico</small></div></li><li><span>3</span><div><strong>Fecha y hora</strong><small>Encuentra un bloque</small></div></li><li><span>4</span><div><strong>Confirmación</strong><small>Revisa tu cita</small></div></li></ol><div class="booking-help"><span>?</span><p><strong>¿Necesitas ayuda?</strong><br>Secretariado puede reservar por ti.</p></div></aside>
        <div class="booking-stage">
            <div class="stage-head"><span>PASO 1 DE 4</span><h2>¿Qué atención necesitas?</h2><p>Selecciona una especialidad para mostrar profesionales disponibles.</p></div>
            <div class="choice-grid">
                @foreach ([['✦','Medicina general','Atención integral'],['◎','Odontología','Salud bucal'],['⌁','Fisioterapia','Rehabilitación'],['◌','Laboratorio','Apoyo diagnóstico'],['✣','Programa PAI','Inmunización'],['⊕','Programa TB','Control y seguimiento']] as $i => $choice)
                <button class="choice-card {{ $i === 0 ? 'selected' : '' }}" type="button"><span>{{ $choice[0] }}</span><div><strong>{{ $choice[1] }}</strong><small>{{ $choice[2] }}</small></div><i>✓</i></button>
                @endforeach
            </div>
            <div class="stage-footer"><span>Puedes cambiar tu selección después.</span><button class="button button-dark" type="button" data-booking-next>Continuar <span>→</span></button></div>
        </div>
    </div>
</div></section>
@endsection
