@extends('layouts.public')
@section('content')<section class="section"><div class="container empty-state"><span>403</span><h1>Este espacio pertenece a otro perfil.</h1><p>Accede a las funciones disponibles para tu cuenta.</p><a class="button button-dark" href="{{ auth()->check()?route('panel'):route('login') }}">Ir a mi panel →</a></div></section>@endsection
