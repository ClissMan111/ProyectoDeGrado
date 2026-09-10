@extends('layouts.public')
@section('title','Especialidades | Villa Israel')
@section('content')
<section class="page-hero"><div class="container page-hero-grid"><div><span class="section-tag light">ATENCIÓN DISPONIBLE</span><h1>Encuentra la atención que necesitas.</h1><p>Conoce nuestras especialidades y consulta los horarios de atención.</p></div><div class="page-hero-index"><span>{{ str_pad($especialidades->count(),2,'0',STR_PAD_LEFT) }}</span><small>especialidades activas</small></div></div></section>
<section class="section catalog-section"><div class="container">
<div class="filter-row"><div><strong>Todas las áreas</strong><span>Atención en Villa Israel</span></div><label class="search-field"><span>⌕</span><input type="search" placeholder="Buscar especialidad" aria-label="Buscar especialidad" data-specialty-search></label></div>
<div class="catalog-grid">@forelse($especialidades as $e)<article class="catalog-card" data-specialty-item><div class="catalog-top"><span class="catalog-icon">✚</span><span class="catalog-number">{{ str_pad($loop->iteration,2,'0',STR_PAD_LEFT) }}</span></div><span class="catalog-type">ATENCIÓN MÉDICA</span><h2>{{ $e->nombre }}</h2><p>{{ $e->descripcion }}</p><div class="tag-list"><span>{{ $e->medicos_count }} {{ $e->medicos_count===1?'profesional':'profesionales' }}</span></div><a href="{{ route('booking',['especialidad_id'=>$e->id]) }}">Consultar horarios <span>→</span></a></article>@empty<p>El centro publicará aquí las especialidades habilitadas.</p>@endforelse</div>
<div class="empty-filter" data-specialty-empty hidden><strong>No encontramos esa especialidad.</strong><span>Prueba con otro nombre.</span></div></div></section>
@endsection
