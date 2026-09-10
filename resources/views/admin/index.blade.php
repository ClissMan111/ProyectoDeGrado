@extends('layouts.dashboard')
@section('title',ucfirst($resource))
@section('content')
@php($titles=['pacientes'=>'Pacientes','medicos'=>'Médicos','especialidades'=>'Especialidades','horarios'=>'Horarios'])
<div class="dashboard-heading"><div><span class="section-tag">ORGANIZA LA ATENCIÓN</span><h1>{{ $titles[$resource] }}</h1><p>{{ $items->total() }} registros · información del centro</p></div>@if($resource!=='pacientes')<a class="button button-dark" href="{{ route('admin.create',$resource) }}">＋ Nuevo registro</a>@endif</div>
<section class="surface">@if($resource!=='horarios')<form class="filter-toolbar"><label>Buscar<input name="q" value="{{ request('q') }}" placeholder="{{ $resource==='especialidades'?'Nombre de especialidad':'Nombre, apellido o CI' }}"></label><button class="button button-dark">Buscar</button><a class="reset-link" href="{{ route('admin.index',$resource) }}">Ver todos</a></form>@endif
<div class="table-scroll"><table class="data-table"><thead><tr><th>{{ $resource==='horarios'?'Médico':'Nombre' }}</th><th>Información</th><th>Estado</th><th><span class="sr-only">Acciones</span></th></tr></thead><tbody>
@forelse($items as $item)<tr><td><strong>{{ $resource==='especialidades'?$item->nombre:($resource==='horarios'?$item->medico->nombre_completo:$item->nombre_completo) }}</strong>@if(in_array($resource,['pacientes','medicos']))<small>{{ $item->usuario->email }}</small>@endif</td><td>
@if($resource==='especialidades'){{ $item->descripcion }}
@elseif($resource==='horarios')<strong>{{ \App\Models\Horario::DIAS[$item->dia_semana] }} · {{ substr($item->hora_inicio,0,5) }} — {{ substr($item->hora_fin,0,5) }}</strong><small>Citas de {{ $item->duracion_cita }} minutos</small>
@else<small>CI {{ $item->ci }} · {{ $item->telefono?:'Sin teléfono' }}</small>@if($resource==='medicos')<div class="tag-list">@foreach($item->especialidades as $e)<span>{{ $e->nombre }}</span>@endforeach</div>@endif @endif
</td><td>@php($active=$resource==='pacientes'?$item->usuario->estado:$item->estado)<span class="status-badge {{ $active?'confirmada':'cancelada' }}">{{ $active?'Activo':'Inactivo' }}</span></td><td><a class="table-link" href="{{ route('admin.edit',[$resource,$item->id]) }}">Editar ↗</a></td></tr>
@empty<tr><td colspan="4"><div class="empty-state"><span>＋</span><h3>Todo empieza con el primer registro</h3><p>{{ $resource==='pacientes'?'Los pacientes aparecerán al crear su cuenta.':'Agrega información para comenzar a organizar la atención.' }}</p></div></td></tr>@endforelse</tbody></table></div>{{ $items->links('partials.pagination') }}</section>
@endsection
