@extends('layouts.dashboard')
@section('title','Gestionar registro')
@section('content')
<div class="dashboard-heading"><div><a class="back-link" href="{{ route('admin.index',$resource) }}">← Volver al listado</a><h1>{{ $item->exists?'Editar':'Nuevo' }} registro</h1><p>Completa los datos de {{ ['medicos'=>'médicos','pacientes'=>'pacientes','especialidades'=>'especialidades','horarios'=>'horarios','indisponibilidades'=>'indisponibilidades médicas'][$resource] }}.</p></div></div>
<form class="surface editor-form" method="post" action="{{ $item->exists?route('admin.update',[$resource,$item->id]):route('admin.store',$resource) }}">@csrf @if($item->exists) @method('PUT') @endif
<div class="form-grid">
@if($resource==='especialidades')
<label class="full"><span>Nombre *</span><input name="nombre" value="{{ old('nombre',$item->nombre) }}" maxlength="100" required></label><label class="full"><span>Descripción</span><textarea name="descripcion" rows="4" maxlength="2000">{{ old('descripcion',$item->descripcion) }}</textarea></label>
@elseif($resource==='indisponibilidades')
<label class="full"><span>Médico *</span><select name="medico_id" required><option value="">Selecciona un médico activo</option>@foreach($medicos as $m)<option value="{{ $m->id }}" @selected(old('medico_id',$item->medico_id??request('medico_id'))==$m->id)>{{ $m->nombre_completo }}</option>@endforeach</select></label>
<label><span>Inicio de la indisponibilidad *</span><input type="datetime-local" name="inicio" value="{{ old('inicio',$item->inicio?->format('Y-m-d\TH:i')) }}" required></label>
<label><span>Finalización de la indisponibilidad *</span><input type="datetime-local" name="fin" value="{{ old('fin',$item->fin?->format('Y-m-d\TH:i')) }}" required></label>
<label class="full"><span>Motivo (opcional)</span><input name="motivo" maxlength="255" value="{{ old('motivo',$item->motivo) }}" placeholder="Feriado, vacaciones, ausencia médica u otro motivo"></label>
<p class="form-hint full">Usa la hora de Bolivia. Si existen citas pendientes o confirmadas en este periodo, deberás gestionarlas antes de activar el bloqueo. Puedes desactivarlo para habilitar nuevamente los horarios.</p>
<a href="{{ route('citas.index',['medico_id'=>$item->medico_id??request('medico_id')]) }}">Consultar citas del médico →</a>
@elseif($resource==='horarios')
<label><span>Médico *</span><select name="medico_id" required>@foreach($medicos as $m)<option value="{{ $m->id }}" @selected(old('medico_id',$item->medico_id??request('medico_id'))==$m->id)>{{ $m->nombre_completo }}</option>@endforeach</select></label>
<label><span>Día de atención *</span><select name="dia_semana" required>@foreach(\App\Models\Horario::DIAS as $n=>$day)<option value="{{ $day }}" @selected(old('dia_semana',$item->dia_semana)==$day)>{{ $day }}</option>@endforeach</select></label>
<label><span>Hora de inicio *</span><input type="time" name="hora_inicio" value="{{ old('hora_inicio',substr($item->hora_inicio??'08:00',0,5)) }}" required></label><label><span>Hora de finalización *</span><input type="time" name="hora_fin" value="{{ old('hora_fin',substr($item->hora_fin??'12:00',0,5)) }}" required></label><label><span>Duración de cada cita (minutos) *</span><input type="number" name="duracion_cita" min="5" max="240" value="{{ old('duracion_cita',$item->duracion_cita??30) }}" required></label>
<p class="form-hint full">Los bloques se repiten semanalmente. Al modificarlos, las citas ya reservadas permanecen en su horario original; revisa la agenda y gestiona cualquier atención afectada.</p>
@else
@foreach(['nombres'=>'Nombres','apellidos'=>'Apellidos','ci'=>'Cédula de identidad','telefono'=>'Teléfono'] as $key=>$label)<label><span>{{ $label }} {{ $key==='telefono'?'(opcional)':'*' }}</span><input name="{{ $key }}" value="{{ old($key,$item->$key) }}" @required($key!=='telefono')></label>@endforeach
@if($resource==='pacientes')<label><span>Fecha de nacimiento</span><input type="date" name="fecha_nacimiento" max="{{ today()->format('Y-m-d') }}" value="{{ old('fecha_nacimiento',$item->fecha_nacimiento?->format('Y-m-d')) }}"></label>@endif
<label><span>Correo electrónico *</span><input type="email" name="email" required value="{{ old('email',$item->usuario?->email) }}"></label>
<label><span>{{ $item->exists?'Nueva contraseña (opcional)':'Contraseña inicial *' }}</span><input type="password" name="password" minlength="8" autocomplete="new-password" @required(!$item->exists)></label><label><span>Confirmar contraseña</span><input type="password" name="password_confirmation" minlength="8" autocomplete="new-password" @required(!$item->exists)></label>
@if($resource==='medicos')<fieldset class="full specialty-picker"><legend>Especialidades del médico *</legend><p class="form-hint">Selecciona una o varias áreas de atención.</p>@forelse($especialidades as $e)<label class="checkbox"><input type="checkbox" name="especialidades[]" value="{{ $e->id }}" @checked(in_array($e->id,old('especialidades',$item->exists?$item->especialidades->pluck('id')->all():[])))><span>{{ $e->nombre }}</span></label>@empty<p>Primero registra una especialidad.</p>@endforelse</fieldset>@endif
@endif
<label><span>Estado *</span><select name="estado"><option value="1" @selected(old('estado',$resource==='pacientes'?$item->usuario?->estado:($item->estado??true)))>Activo</option><option value="0" @selected(!old('estado',$resource==='pacientes'?$item->usuario?->estado:($item->estado??true)))>Inactivo</option></select></label>
</div><div class="form-footer"><button class="button button-dark">Guardar cambios →</button><a href="{{ route('admin.index',$resource) }}">Cancelar</a></div></form>
@endsection
