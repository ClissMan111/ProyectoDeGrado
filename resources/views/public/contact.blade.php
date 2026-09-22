@extends('layouts.public')

@section('title', 'Contacto | Villa Israel')

@section('content')

<section class="institution-hero contact-photo">
    <div class="container">

        <span class="section-tag light">CONTACTO</span>

        <h1>Estamos para orientarte.</h1>

        <p>
            La información del centro, reunida en un solo lugar
            para preparar tu visita.
        </p>

        <div class="institution-location">
            <x-icon name="pin"/>
            {{ config('centro.city') }}
        </div>

    </div>
</section>


<section class="section">

    <div class="container contact-directory">

        <div>

            <span class="section-tag">A TU ALCANCE</span>

            <h2>Encuentra tu próximo paso.</h2>

            <p class="institution-lead">
                Para consultas sobre tus reservas o correcciones de datos,
                puedes acudir a {{ config('centro.reception') }}.
            </p>


            <div class="contact-detail-grid">

                {{-- DIRECCIÓN --}}
                <article class="contact-detail-card contact-address">

                    <span class="contact-card-icon">
                        <x-icon name="pin"/>
                    </span>

                    <div>

                        <small>VISÍTANOS</small>

                        <h3>Dirección del centro</h3>

                        <p>
                            {{ config('centro.address') }}
                            <br>
                            {{ config('centro.city') }}
                        </p>

                        <button
                            class="contact-copy"
                            type="button"
                            data-copy-address="{{ config('centro.address') }}, {{ config('centro.city') }}"
                        >
                            <x-icon name="copy"/>
                            Copiar dirección
                        </button>

                        <span
                            class="copy-status"
                            aria-live="polite"
                            data-copy-status
                        ></span>

                    </div>

                </article>


                {{-- TELÉFONO --}}
                <article class="contact-detail-card">

                    <span class="contact-card-icon">
                        <x-icon name="phone"/>
                    </span>

                    <div>

                        <small>LLÁMANOS</small>

                        <h3>Teléfono</h3>

                        @if(config('centro.phone'))

                            <a href="tel:{{ preg_replace('/[^+0-9]/', '', config('centro.phone')) }}">
                                {{ config('centro.phone') }}
                            </a>

                        @else

                            <p class="contact-pending">
                                Por confirmar
                            </p>

                            <p class="contact-note">
                                El centro todavía no ha proporcionado
                                un número oficial.
                            </p>

                        @endif

                    </div>

                </article>


                {{-- WHATSAPP --}}
                <article class="contact-detail-card">

                    <span class="contact-card-icon">
                        <x-icon name="message"/>
                    </span>

                    <div>

                        <small>MENSAJERÍA</small>

                        <h3>WhatsApp</h3>

                        @if(config('centro.whatsapp'))

                            <a
                                href="https://wa.me/{{ preg_replace('/[^0-9]/', '', config('centro.whatsapp')) }}"
                                target="_blank"
                                rel="noopener"
                            >
                                {{ config('centro.whatsapp') }} ↗
                            </a>

                        @else

                            <p class="contact-pending">
                                Por confirmar
                            </p>

                            <p class="contact-note">
                                Canal pendiente de habilitación por el centro.
                            </p>

                        @endif

                    </div>

                </article>


                {{-- CORREO --}}
                <article class="contact-detail-card contact-email">

                    <span class="contact-card-icon">
                        <x-icon name="mail"/>
                    </span>

                    <div>

                        <small>ESCRÍBENOS</small>

                        <h3>Correo electrónico</h3>

                        @if(config('centro.email'))

                            <a href="mailto:{{ config('centro.email') }}">
                                {{ config('centro.email') }}
                            </a>

                        @else

                            <p>
                                {{ config('centro.example_email') }}
                            </p>

                            <span class="example-label">
                                Ejemplo · no operativo
                            </span>

                        @endif

                    </div>

                </article>


                {{-- MAPA --}}
                <article
                    class="contact-map-card"
                    aria-labelledby="contact-map-title"
                >

                    <div class="contact-map-heading">

                        <span class="contact-card-icon">
                            <x-icon name="pin"/>
                        </span>

                        <div>
                            <small>ENCUÉNTRANOS</small>
                            <h3 id="contact-map-title">
                                Ubicación del centro
                            </h3>
                        </div>

                    </div>


                    {{-- MAPA OFICIAL DE GOOGLE MAPS --}}
                    <iframe
                        class="contact-map-frame"
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d36204.40523792085!2d-66.19275408205262!3d-17.482705964575587!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x93e36d892e32f18d%3A0xc1dd2db07d2a273d!2sCentro%20de%20Salud%20Villa%20Israel!5e0!3m2!1ses-419!2sbo!4v1790112823220!5m2!1ses-419!2sbo"
                        title="Mapa de ubicación del Centro de Salud Villa Israel"
                        width="640"
                        height="320"
                        style="border:0;"
                        loading="lazy"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allowfullscreen
                    ></iframe>


                    <a
                        class="contact-map-link"
                        href="https://www.google.com/maps/search/?api=1&query=Centro+de+Salud+Villa+Israel+Cochabamba+Bolivia"
                        target="_blank"
                        rel="noopener"
                    >
                        Abrir mapa completo
                        <x-icon name="up-right"/>
                    </a>

                </article>

            </div>

        </div>


        {{-- PANEL LATERAL --}}
        <aside class="contact-visit-panel">

            <div class="contact-visit-photo">

                <img
                    src="{{ asset('images/care/orientacion-medica.jpg') }}"
                    alt="Centro de Salud Villa Israel"
                    width="1600"
                    height="1089"
                    loading="lazy"
                >

            </div>


            <div class="contact-visit-content">

                <span class="section-tag">
                    COCHABAMBA · BOLIVIA
                </span>

                <h2>
                    Ubícanos antes
                    <br>
                    de salir.
                </h2>

                <p>
                    {{ config('centro.address') }}.
                </p>


                <a
                    class="button button-dark button-wide"
                    href="https://www.google.com/maps/search/?api=1&query=Centro+de+Salud+Villa+Israel+Cochabamba+Bolivia"
                    target="_blank"
                    rel="noopener"
                >
                    Buscar dirección en el mapa
                    <x-icon name="up-right"/>
                </a>


                <div class="contact-reception">

                    <x-icon name="users"/>

                    <span>
                        Atención presencial
                        <strong>
                            {{ config('centro.reception') }}
                        </strong>
                    </span>

                </div>


                <a
                    class="text-link dark"
                    href="{{ route('faq') }}"
                >
                    Preguntas frecuentes
                    <span>→</span>
                </a>

            </div>

        </aside>

    </div>

</section>

@endsection