@extends('layouts.dashboard')
@section('title',match(auth()->user()->rol){'administrador'=>'Panel administrativo','medico'=>'Mi jornada',default=>'Mi espacio'})
@section('content')
@php($panel=match(auth()->user()->rol){'administrador'=>'admin','medico'=>'doctor',default=>'patient'})
<div class="vi-dashboard vi-dashboard-{{ $panel }}">
@include('dashboards.'.$panel)
</div>
@endsection
