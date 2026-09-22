@props(['light' => false])
<span {{ $attributes->class(['brand-mark', 'brand-emblem']) }} aria-hidden="true"><img src="{{ asset($light ? 'images/brand/villa-israel-light.svg' : 'images/brand/villa-israel.svg') }}" width="80" height="80" alt="" decoding="async"></span>
