@props(['image' => 'consulta-digital', 'alt' => '', 'eager' => false])
<figure {{ $attributes->class(['care-photo']) }}><img src="{{ asset('images/care/'.$image.'.jpg') }}" alt="{{ $alt }}" width="1200" height="{{ in_array($image,['orientacion-medica','instrumental-medico']) ? 1800 : 800 }}" loading="{{ $eager ? 'eager' : 'lazy' }}" decoding="async">{{ $slot }}</figure>
