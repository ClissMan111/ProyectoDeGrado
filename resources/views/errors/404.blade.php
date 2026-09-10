@extends('layouts.public')
@section('content')<section class="section"><div class="container empty-state"><span>404</span><h1>No encontramos esa página.</h1><p>Puedes volver al inicio y continuar desde allí.</p><a class="button button-dark" href="{{ route('home') }}">Volver al inicio →</a></div></section>@endsection
