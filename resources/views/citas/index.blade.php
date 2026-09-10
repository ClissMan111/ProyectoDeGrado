@extends('layouts.dashboard')
@section('title','Citas y agenda')
@section('content')
<div class="dashboard-heading"><div><span class="section-tag">SEGUIMIENTO DE ATENCIÓN</span><h1>{{ auth()->user()->rol==='medico'?'Mi agenda':'Citas e historial' }}</h1><p>Consulta fechas, estados y el detalle de cada reserva.</p></div>@if(auth()->user()->rol!=='medico')<a class="button button-dark" href="{{ route('booking') }}">＋ Reservar cita</a>@endif</div>
<section class="surface"><form class="filter-toolbar" method="get"><label>Fecha<input type="date" name="fecha" value="{{ request('fecha') }}"></label><label>Estado<select name="estado"><option value="">Todos</option>@foreach(\App\Models\Cita::ESTADOS as $key=>$label)<option value="{{ $key }}" @selected(request('estado')===$key)>{{ $label }}</option>@endforeach</select></label>
@if(auth()->user()->rol==='administrador')<label>Médico<select name="medico_id"><option value="">Todos</option>@foreach($medicos as $medico)<option value="{{ $medico->id }}" @selected(request('medico_id')==$medico->id)>{{ $medico->nombre_completo }}</option>@endforeach</select></label>@endif
@if(auth()->user()->rol!=='paciente')<label>Paciente<input name="q" value="{{ request('q') }}" placeholder="Nombre o CI"></label>@endif
<button class="button button-dark">Filtrar</button><a class="reset-link" href="{{ route('citas.index') }}">Limpiar</a></form>@include('partials.citas-table')</section>
@endsection
