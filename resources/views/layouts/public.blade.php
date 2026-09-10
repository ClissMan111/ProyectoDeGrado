<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b3b68">
    <meta name="description" content="Reserva de citas médicas del Centro de Salud Villa Israel, Cochabamba.">
    <title>@yield('title', 'Centro de Salud Villa Israel')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="public-body">
    <a class="skip-link" href="#contenido">Saltar al contenido</a>
    <div class="utility-bar">
        <div class="container utility-inner">
            <span>Cochabamba · Av. Panamericana y Gral. Bartolomé Salomón</span>
            <span>Lunes a viernes · 07:30–18:00</span>
        </div>
    </div>
    <header class="site-header" data-header>
        <div class="container header-inner">
            <a class="brand" href="{{ route('home') }}" aria-label="Villa Israel, página principal">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 48 48"><path d="M19 8h10v11h11v10H29v11H19V29H8V19h11z"/></svg>
                </span>
                <span><strong>Villa Israel</strong><small>Centro de Salud</small></span>
            </a>
            <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="main-nav" data-nav-toggle>
                <span></span><span></span><span></span><span class="sr-only">Abrir menú</span>
            </button>
            <nav class="main-nav" id="main-nav" data-nav>
                <a class="{{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Inicio</a>
                <a class="{{ request()->routeIs('specialties') ? 'active' : '' }}" href="{{ route('specialties') }}">Especialidades</a>
                <a class="{{ request()->routeIs('how-it-works') ? 'active' : '' }}" href="{{ route('how-it-works') }}">Cómo reservar</a>
                <a class="{{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">El centro</a>
                @auth<a class="nav-login" href="{{ route('panel') }}">Mi panel</a>@else<a class="nav-login {{ request()->routeIs('login') ? 'active' : '' }}" href="{{ route('login') }}">Ingresar</a>@endauth
                <a class="button button-small" href="{{ route('booking') }}">Reservar cita</a>
            </nav>
        </div>
    </header>

    <main id="contenido">@yield('content')</main>

    <footer class="site-footer">
        <div class="container footer-grid">
            <div>
                <a class="brand brand-light" href="{{ route('home') }}">
                    <span class="brand-mark"><svg viewBox="0 0 48 48"><path d="M19 8h10v11h11v10H29v11H19V29H8V19h11z"/></svg></span>
                    <span><strong>Villa Israel</strong><small>Centro de Salud</small></span>
                </a>
                <p>Atención organizada, cercana y accesible para nuestra comunidad.</p>
            </div>
            <div><h2>Atención</h2><a href="{{ route('specialties') }}">Especialidades</a><a href="{{ route('how-it-works') }}">Cómo reservar</a><a href="{{ route('faq') }}">Preguntas frecuentes</a></div>
            <div><h2>Contacto</h2><p>Av. Panamericana y Gral. Bartolomé Salomón</p><p>Cochabamba, Bolivia</p><a href="{{ route('contact') }}">Ver información de contacto</a></div>
            <div class="footer-action"><span>¿Necesitas una consulta?</span><a class="button button-light" href="{{ route('booking') }}">Ver horarios disponibles</a></div>
        </div>
        <div class="container footer-bottom"><span>© {{ date('Y') }} Centro de Salud Villa Israel</span><span>Sistema de reserva de citas médicas</span></div>
    </footer>
</body>
</html>
