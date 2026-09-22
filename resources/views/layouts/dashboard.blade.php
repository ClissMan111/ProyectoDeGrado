@php
$role = match(auth()->user()->rol) {'administrador'=>'admin', 'medico'=>'doctor', default=>'patient'};
$label = match($role) {'admin'=>'Administración', 'doctor'=>'Equipo médico', default=>'Espacio del paciente'};
$initials = collect(explode(' ',auth()->user()->name))->filter()->take(2)->map(fn($s)=>mb_substr($s,0,1))->join('');
$links = [
 ['label'=>'Mi resumen','icon'=>'grid','url'=>route(auth()->user()->panel()),'active'=>request()->routeIs('dashboard.*')],
 ['label'=>$role==='doctor'?'Mi agenda':($role==='patient'?'Mis citas':'Agenda de citas'),'icon'=>'calendar','url'=>route('citas.index'),'active'=>request()->routeIs('citas.*')],
];
if($role==='admin') $management = ['pacientes'=>['Pacientes','users'],'medicos'=>['Médicos','doctor'],'especialidades'=>['Especialidades','heart'],'horarios'=>['Horarios','clock'],'indisponibilidades'=>['Indisponibilidades','block']];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#082f55">
<title>@yield('title','Mi panel') · Villa Israel</title>
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
@vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="dashboard-body vi-workspace vi-{{ $role }}" data-workspace>
<a class="skip-link" href="#contenido">Saltar al contenido</a>
<div class="vi-shell">
<button class="dashboard-backdrop" data-dashboard-close aria-label="Cerrar navegación" hidden></button>
<aside class="vi-sidebar" id="dashboard-nav" data-dashboard-nav aria-label="Navegación principal">
<div class="vi-brand-row"><a class="vi-brand" href="{{ route(auth()->user()->panel()) }}" aria-label="Villa Israel, inicio"><x-brand-mark light/><span class="vi-brand-text"><strong>Villa Israel</strong><small>CENTRO DE SALUD</small></span></a><button type="button" class="vi-mobile-close vi-icon-button" data-dashboard-close aria-label="Cerrar navegación"><x-icon name="close"/></button></div>
<div class="vi-workspace-label"><span class="vi-role-dot"></span><span class="vi-nav-text">{{ $label }}</span></div>
@if($role!=='doctor')<a href="{{ route('booking') }}" class="vi-book-link {{ request()->routeIs('booking')?'active':'' }}" data-command title="Reservar cita"><x-icon name="plus"/><span class="vi-nav-text">{{ $role==='admin'?'Nueva cita':'Reservar una cita' }}</span><span class="vi-shortcut">↗</span></a>@endif
<nav class="vi-navigation">
<p class="vi-nav-heading">{{ $role==='patient'?'MI ATENCIÓN':'MI JORNADA' }}</p>
@foreach($links as $link)<a href="{{ $link['url'] }}" class="vi-nav-link {{ $link['active']?'active':'' }}" @if($link['active']) aria-current="page" @endif title="{{ $link['label'] }}" data-command><x-icon :name="$link['icon']"/><span class="vi-nav-text">{{ $link['label'] }}</span><span class="vi-nav-indicator"></span></a>@endforeach
@if($role==='admin')<p class="vi-nav-heading">ORGANIZACIÓN DEL CENTRO</p>
@foreach($management as $key=>[$title,$icon])<a class="vi-nav-link {{ request()->route('resource')===$key?'active':'' }}" href="{{ route('admin.index',$key) }}" @if(request()->route('resource')===$key) aria-current="page" @endif title="{{ $title }}" data-command><x-icon :name="$icon"/><span class="vi-nav-text">{{ $title }}</span><span class="vi-nav-indicator"></span></a>@endforeach
<a class="vi-nav-link {{ request()->routeIs('reports')?'active':'' }}" href="{{ route('reports') }}" title="Reportes" data-command><x-icon name="chart"/><span class="vi-nav-text">Reportes</span><span class="vi-nav-indicator"></span></a>
@endif
<p class="vi-nav-heading">CONOCE EL CENTRO</p>
<a class="vi-nav-link" href="{{ route('about') }}" title="Acerca de nosotros" data-command><x-icon name="home"/><span class="vi-nav-text">Acerca de nosotros</span></a>
<a class="vi-nav-link" href="{{ route('contact') }}" title="Contacto" data-command><x-icon name="phone"/><span class="vi-nav-text">Contacto</span></a>
<p class="vi-nav-heading">A TU ALCANCE</p>
<a class="vi-nav-link {{ request()->routeIs('account*')?'active':'' }}" href="{{ route('account') }}" title="Cuenta y seguridad" data-command><x-icon name="settings"/><span class="vi-nav-text">Cuenta y seguridad</span></a>
<a class="vi-nav-link" href="{{ route('faq') }}" title="Ayuda" data-command><x-icon name="help"/><span class="vi-nav-text">Centro de ayuda</span><x-icon name="up-right" class="vi-nav-external"/></a>
</nav>
<div class="vi-sidebar-bottom"><div class="vi-location"><span class="vi-location-dot"></span><span class="vi-nav-text">Cochabamba, Bolivia</span></div><div class="vi-sidebar-account"><a href="{{ route('account') }}" class="vi-account-link" title="{{ auth()->user()->name }}"><span class="vi-avatar">{{ $initials }}</span><span class="vi-nav-text"><strong>{{ auth()->user()->name }}</strong><small>{{ $label }}</small></span></a><form method="post" action="{{ route('logout') }}">@csrf<button type="submit" class="vi-icon-button" title="Cerrar sesión" aria-label="Cerrar sesión"><x-icon name="logout"/></button></form></div></div>
</aside>
<div class="vi-main" data-dashboard-main>
<header class="vi-topbar">
<div class="vi-topbar-start"><button class="vi-icon-button vi-menu-toggle" type="button" data-dashboard-toggle aria-label="Abrir menú" aria-controls="dashboard-nav" aria-expanded="false"><x-icon name="menu"/></button><button class="vi-icon-button vi-collapse-toggle" type="button" data-sidebar-collapse aria-label="Reducir navegación" aria-expanded="true"><x-icon name="panel"/></button><div class="vi-breadcrumb"><span>Villa Israel</span><span>/</span><strong>@yield('title','Mi resumen')</strong></div></div>
<div class="vi-topbar-actions"><button type="button" class="vi-search-trigger" data-command-open aria-label="Buscar una sección (Ctrl K)"><x-icon name="search"/><span>Ir a una sección</span><kbd>Ctrl K</kbd></button><span class="vi-topbar-date">{{ now()->locale('es')->isoFormat('D MMM') }}</span><details class="vi-profile-menu"><summary aria-label="Menú de mi cuenta"><span class="vi-avatar vi-avatar-small">{{ $initials }}</span></summary><div class="vi-profile-dropdown"><small>SESIÓN PERSONAL</small><strong>{{ auth()->user()->name }}</strong><a href="{{ route('account') }}"><x-icon name="settings"/>Cuenta y seguridad</a><a href="{{ route('home') }}"><x-icon name="up-right"/>Ir al sitio del centro</a><form method="post" action="{{ route('logout') }}">@csrf<button type="submit"><x-icon name="logout"/>Cerrar sesión</button></form></div></details></div>
</header>
<main class="vi-content" id="contenido">@include('partials.feedback') @yield('content')<footer class="vi-page-footer"><span>Villa Israel <span class="vi-footer-dot">·</span> Cuidamos de nuestra comunidad.</span><a href="{{ route('contact') }}">Orientación y contacto <x-icon name="up-right"/></a></footer></main>
</div>
</div>
<dialog class="vi-command-dialog" data-command-dialog aria-labelledby="command-title"><div class="vi-command-head"><x-icon name="search"/><label class="sr-only" id="command-title" for="command-search">Buscar una sección</label><input id="command-search" type="search" placeholder="¿A dónde quieres ir?" autocomplete="off" data-command-search><button type="button" data-command-close aria-label="Cerrar búsqueda"><kbd>Esc</kbd></button></div><p class="vi-command-caption">ACCESOS DE TU ESPACIO</p><div class="vi-command-results" data-command-results></div><p class="vi-command-empty" data-command-empty hidden>No encontramos esa sección. Prueba con otro nombre.</p><div class="vi-command-footer"><span>↑ ↓ para elegir</span><span>Enter para abrir</span></div></dialog>
@include('partials.visual-controls')
</body></html>
