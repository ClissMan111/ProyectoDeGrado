@extends('layouts.public')

@section('title', 'Villa Israel | Reserva tu cita médica')

@section('content')
<section class="hero" id="reserva">
    <div class="container hero-grid">
        <div class="hero-copy">
            <div class="eyebrow"><span></span> Atención médica más cerca de ti</div>
            <h1>Tu cita empieza <em>sin filas.</em></h1>
            <p class="hero-lead">Elige la atención que necesitas, encuentra un horario y reserva desde donde estés. Simple, claro y pensado para nuestra comunidad.</p>
            <div class="hero-actions">
                <a class="button button-primary" href="{{ route('booking') }}">Buscar una cita <span>→</span></a>
                <a class="text-link" href="{{ route('how-it-works') }}">Conocer el proceso <span>→</span></a>
            </div>
            <div class="hero-assurance">
                <span class="avatar-stack"><i>VI</i><i>+</i><i>24</i></span>
                <p><strong>Información segura</strong><br>Acceso personal y protegido</p>
            </div>
        </div>

        <div class="booking-card" aria-label="Acceso rápido a reserva">
            <div class="booking-top">
                <div><span class="card-kicker">RESERVA RÁPIDA</span><h2>¿Qué atención buscas?</h2></div>
                <span class="open-badge"><i></i> Reserva en línea</span>
            </div>
            <form method="get" action="{{ route('booking') }}" class="quick-search">
            <label>Especialidad<select name="especialidad_id" required><option value="">Elige un área de atención</option>@foreach($especialidades as $e)<option value="{{ $e->id }}">{{ $e->nombre }}</option>@endforeach</select></label>
            <label>Fecha preferida<input type="date" name="fecha" required min="{{ today()->format('Y-m-d') }}" max="{{ today()->addDays(config('citas.horizonte_dias'))->format('Y-m-d') }}" value="{{ today()->format('Y-m-d') }}"></label>
            <button class="button button-dark button-wide">Consultar disponibilidad →</button>
            </form>
            <p class="privacy-note"><svg viewBox="0 0 24 24"><path d="M7 10V7a5 5 0 0110 0v3M6 10h12v10H6z"/></svg> Tus datos se utilizan únicamente para gestionar tu atención.</p>
        </div>
    </div>
    <div class="hero-orbit orbit-one"></div><div class="hero-orbit orbit-two"></div>
</section>

<section class="care-strip">
    <div class="container care-grid">
        <div><strong>{{ $especialidades->count() }}</strong><span>servicios de salud</span></div>
        <div><strong>3 pasos</strong><span>para reservar</span></div>
        <div><strong>24/7</strong><span>consulta tus citas</span></div>
        <div class="care-message"><i>✓</i><span><strong>Sin desplazarte para obtener ficha.</strong><br>Tu tiempo también es parte del cuidado.</span></div>
    </div>
</section>

<section class="section specialties-section" id="especialidades">
    <div class="container">
        <div class="section-heading split-heading"><div><span class="section-tag">NUESTRAS ESPECIALIDADES</span><h2>Atención para cada etapa de tu salud.</h2></div><p>Encuentra profesionales y horarios disponibles según el servicio que necesitas.</p></div>
        <div class="specialty-grid">
        @forelse($especialidades->take(4) as $e)
        <article class="specialty-card {{ $loop->first?'featured':'' }}"><span class="specialty-number">{{ str_pad($loop->iteration,2,'0',STR_PAD_LEFT) }}</span><div class="specialty-icon">✚</div><h3>{{ $e->nombre }}</h3><p>{{ $e->descripcion }}</p><a href="{{ route('booking',['especialidad_id'=>$e->id]) }}">Ver disponibilidad <span>↗</span></a></article>
        @empty<p>Las especialidades se publicarán al habilitar la atención.</p>@endforelse
        </div>
    </div>
</section>

<section class="section process-section" id="como-funciona">
    <div class="container process-grid">
        <div class="process-intro"><span class="section-tag light">RESERVAR ES FÁCIL</span><h2>Tres decisiones.<br>Una reserva organizada.</h2><p>El sistema te muestra solo horarios realmente disponibles para evitar cruces y esperas innecesarias.</p><a class="button button-light" href="{{ route('booking') }}">Comenzar ahora</a></div>
        <ol class="process-list">
            <li><span>01</span><div><h3>Elige tu especialidad</h3><p>Indica qué atención necesitas y conoce a los profesionales disponibles.</p></div></li>
            <li><span>02</span><div><h3>Selecciona fecha y hora</h3><p>Compara alternativas y escoge el horario que mejor se adapte a ti.</p></div></li>
            <li><span>03</span><div><h3>Confirma tu reserva</h3><p>Revisa los datos y encuentra tu cita guardada en tu panel personal.</p></div></li>
        </ol>
    </div>
</section>

<section class="section center-section" id="centro">
    <div class="container center-grid">
        <div class="center-visual"><div class="visual-cross">+</div><span class="visual-label">Centro de Salud<br><strong>Villa Israel</strong></span><div class="visual-card"><small>HOY</small><strong>Atención continua</strong><span>07:30 — 18:00</span></div></div>
        <div class="center-copy"><span class="section-tag">CUIDADO DE BARRIO</span><h2>Un centro cercano, ahora también digital.</h2><p>Organizamos la atención para que pacientes, médicos y personal del centro compartan información clara y actualizada.</p><ul class="check-list"><li><i>✓</i> Consulta tus próximas citas en cualquier momento</li><li><i>✓</i> Reprograma o cancela desde tu cuenta</li><li><i>✓</i> Accede desde celular, tableta o computadora</li></ul><a class="text-link dark" href="{{ route('about') }}">Conoce el Centro de Salud <span>→</span></a></div>
    </div>
</section>

<section class="final-cta"><div class="container final-cta-inner"><div><span>¿LISTO PARA ORGANIZAR TU ATENCIÓN?</span><h2>Reserva sin filas. Llega a la hora indicada.</h2></div><a class="button button-light" href="{{ route('booking') }}">Buscar disponibilidad <span>→</span></a></div></section>
@endsection
