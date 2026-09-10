@php
$role=match(auth()->user()->rol){'administrador'=>'admin','medico'=>'doctor',default=>'patient'};
$label=match($role){'admin'=>'Administración','doctor'=>'Médico',default=>'Paciente'};
$initials=collect(explode(' ',auth()->user()->name))->take(2)->map(fn($s)=>mb_substr($s,0,1))->join('');
@endphp
<!DOCTYPE html><html lang="es"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#082f55">
<title>@yield('title','Mi panel') | Villa Israel</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
@vite(['resources/css/app.css','resources/js/app.js'])
</head><body class="dashboard-body {{ $role }}-accent">
<a class="skip-link" href="#contenido">Saltar al contenido</a>
<div class="dashboard-shell">
<button class="dashboard-backdrop" data-dashboard-close aria-label="Cerrar menú" hidden></button>
<aside class="dashboard-sidebar" id="dashboard-nav" data-dashboard-nav>
<button type="button" class="sidebar-close" data-dashboard-close aria-label="Cerrar navegación">×</button>
<div class="dashboard-brand"><a class="brand brand-light" href="{{ route('home') }}"><span class="brand-mark"><svg viewBox="0 0 48 48"><path d="M19 8h10v11h11v10H29v11H19V29H8V19h11z"/></svg></span><span><strong>Villa Israel</strong><small>Centro de Salud</small></span></a></div>
<div class="role-chip"><span>{{ $initials }}</span><div><small>MI ESPACIO</small><strong>{{ $label }}</strong></div></div>
<nav class="dashboard-nav" aria-label="Navegación del panel">
<span class="nav-section">ATENCIÓN Y GESTIÓN</span>
<a href="{{ route(auth()->user()->panel()) }}" class="{{ request()->routeIs('dashboard.*')?'active':'' }}"><i>⌂</i> Resumen</a>
@if($role!=='doctor')<a href="{{ route('booking') }}" class="{{ request()->routeIs('booking')?'active':'' }}"><i>＋</i> Reservar cita</a>@endif
<a href="{{ route('citas.index') }}" class="{{ request()->routeIs('citas.*')?'active':'' }}"><i>▦</i> {{ $role==='doctor'?'Mi agenda':($role==='patient'?'Mis citas e historial':'Citas') }}</a>
@if($role==='admin')
@foreach(['pacientes'=>'Pacientes','medicos'=>'Médicos','especialidades'=>'Especialidades','horarios'=>'Horarios'] as $key=>$title)
<a href="{{ route('admin.index',$key) }}" class="{{ request()->route('resource')===$key?'active':'' }}"><i>{{ ['pacientes'=>'♙','medicos'=>'✚','especialidades'=>'✦','horarios'=>'◷'][$key] }}</i> {{ $title }}</a>
@endforeach
<a href="{{ route('reports') }}" class="{{ request()->routeIs('reports')?'active':'' }}"><i>↗</i> Reportes</a>
@endif
<span class="nav-section">MI CUENTA</span>
<a href="{{ route('account') }}" class="{{ request()->routeIs('account')?'active':'' }}"><i>⚙</i> Cuenta y seguridad</a>
<form method="post" action="{{ route('logout') }}">@csrf<button class="nav-logout" type="submit">← &nbsp; Cerrar sesión</button></form>
</nav>
<a class="sidebar-support" href="{{ route('faq') }}"><span>?</span><div><strong>Estamos para ayudarte</strong><small>Preguntas frecuentes ↗</small></div></a>
</aside>
<div class="dashboard-main"><header class="dashboard-topbar">
<button class="dashboard-menu" type="button" data-dashboard-toggle aria-label="Abrir menú" aria-controls="dashboard-nav" aria-expanded="false">☰</button>
<div class="topbar-path"><span>Villa Israel</span><b>/</b><strong>{{ $label }}</strong></div>
<a class="user-menu" href="{{ route('account') }}"><span>{{ $initials }}</span><div><strong>{{ auth()->user()->name }}</strong><small>{{ $label }}</small></div></a>
</header><main class="dashboard-content" id="contenido">@include('partials.feedback') @yield('content')</main></div>
</div></body></html>
