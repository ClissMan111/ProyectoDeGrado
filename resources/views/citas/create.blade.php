@extends('layouts.dashboard')
@section('title',$cita?'Reprogramar cita':'Reservar cita')
@section('content')
<div class="dashboard-heading"><div><span class="section-tag">UN PASO MÁS CERCA</span><h1>{{ $cita?'Elige un nuevo horario.':'Reserva tu próxima atención.' }}</h1><p>Selecciona una especialidad, compara horarios y revisa tu reserva.</p></div></div>
<div class="reservation-layout"><section class="surface"><form method="post" action="{{ $cita?route('citas.reschedule.save',$cita):route('citas.store') }}" data-reservation data-url="{{ route('availability') }}" data-cita="{{ $cita?->id }}">@csrf @if($cita) @method('PUT') @endif
<div class="step-heading"><span>01</span><div><h2>¿Qué atención necesitas?</h2><p>La disponibilidad se actualiza según la agenda del centro.</p></div></div>
<div class="form-grid">
@if(auth()->user()->rol==='administrador')<label class="full"><span>Paciente</span><select name="paciente_id" required><option value="">Selecciona un paciente registrado</option>@foreach($pacientes as $p)<option value="{{ $p->id }}" @selected(old('paciente_id')==$p->id)>{{ $p->nombre_completo }} · CI {{ $p->ci }}</option>@endforeach</select></label>@endif
<label><span>Especialidad</span><select name="especialidad_id" required data-specialty @if($cita) aria-readonly="true" @endif><option value="">Selecciona una especialidad</option>@foreach($especialidades as $e) @if(!$cita || $cita->especialidad_id===$e->id)<option value="{{ $e->id }}" @selected(($cita?->especialidad_id??old('especialidad_id',request('especialidad_id')))==$e->id)>{{ $e->nombre }}</option>@endif @endforeach</select></label>
<label><span>Fecha</span><input type="date" name="fecha" data-date required min="{{ today()->format('Y-m-d') }}" value="{{ old('fecha',request('fecha',$cita?->fecha->format('Y-m-d')??today()->format('Y-m-d'))) }}"></label></div>
<div class="step-heading"><span>02</span><div><h2>Elige médico y horario</h2><p>Todos los horarios se muestran en hora de Bolivia.</p></div></div>
<div data-slots class="slot-results" aria-live="polite"><p class="empty-state">Selecciona una especialidad para consultar horarios.</p></div>
<input type="hidden" name="medico_id" data-medico><input type="hidden" name="hora_inicio" data-time>
<div class="reservation-summary" data-summary hidden></div>
<button class="button button-dark button-wide" data-confirm disabled>{{ $cita?'Confirmar reprogramación':'Confirmar reserva' }} →</button>
<noscript><p>Activa JavaScript para consultar los horarios disponibles.</p></noscript></form></section>
<aside class="reservation-aside"><x-care-photo image="instrumental-medico" class="reservation-photo" alt="Estetoscopio sobre una mesa de consulta"/><span class="section-tag light">TE ACOMPAÑAMOS</span><h2>Un horario que encaje contigo.</h2><p>@if($cita)La reprogramación conserva el estado actual de tu cita: {{ \App\Models\Cita::ESTADOS[$cita->estado] }}.@else Tu reserva se registra como pendiente. Podrás consultar la confirmación en tu panel.@endif</p><ul><li>Trae tu documento de identidad.</li><li>Revisa los datos antes de confirmar.</li><li>Reserva o elige el nuevo horario con al menos 1 hora de anticipación.</li><li>@if(config('citas.cambios_minutos')>0)Puedes cancelar o reprogramar hasta {{ config('citas.cambios_minutos') }} minutos antes del inicio.@else Puedes cancelar o reprogramar antes del inicio de la cita.@endif</li></ul><a href="{{ route('how-it-works') }}">Cómo reservar ↗</a></aside></div>
@endsection
