@props(['name' => 'arrow', 'class' => ''])
@php
$paths = [
'phone'=>'<path d="M8 3 5 4C2 9 8 20 16 21l3-3-4-4-3 2a12 12 0 0 1-4-5l2-2z"/>',
'mail'=>'<rect x="3" y="5" width="18" height="14" rx="3"/><path d="m3 7 9 6 9-6"/>',
'message'=>'<path d="M21 11a9 9 0 0 1-9 9H4l-2 2V11a9 9 0 1 1 19 0Z"/><path d="M7 10h10M7 14h6"/>',
'copy'=>'<rect x="8" y="8" width="13" height="13" rx="2"/><path d="M16 8V3H3v13h5"/>',

'home'=>'<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/>',
'calendar'=>'<rect x="3" y="5" width="18" height="16" rx="3"/><path d="M7 3v4m10-4v4M3 11h18m-13 4h2m4 0h2m-8 3h2"/>',
'plus'=>'<path d="M12 5v14M5 12h14"/>',
'users'=>'<circle cx="9" cy="8" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3M16 5a3 3 0 0 1 0 6m2 3a5 5 0 0 1 3 5v2"/>',
'doctor'=>'<path d="M6 3v2m12-2v2M4 4v6a8 8 0 0 0 16 0V4h-4M4 4h4m4 14v1a3 3 0 0 0 6 0v-2"/><circle cx="18" cy="15" r="2"/>',
'grid'=>'<rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/>',
'clock'=>'<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
'block'=>'<circle cx="12" cy="12" r="9"/><path d="m6 6 12 12"/>',
'chart'=>'<path d="M4 3v18h17M8 16v-5m5 5V7m5 9V4"/>',
'settings'=>'<path d="M4 7h16M4 17h16"/><circle cx="9" cy="7" r="3"/><circle cx="15" cy="17" r="3"/>',
'logout'=>'<path d="M9 4H4v16h5m5-12 4 4-4 4m-6-4h12"/>',
'arrow'=>'<path d="M4 12h16m-6-6 6 6-6 6"/>',
'up-right'=>'<path d="M6 18 18 6H7m11 0v11"/>',
'chevron'=>'<path d="m9 5 7 7-7 7"/>',
'search'=>'<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>',
'close'=>'<path d="m6 6 12 12M6 18 18 6"/>',
'menu'=>'<path d="M4 6h16M4 12h16M4 18h10"/>',
'panel'=>'<rect x="3" y="4" width="18" height="16" rx="3"/><path d="M9 4v16m5-12 3 4-3 4"/>',
'check'=>'<path d="m5 12 4 4L19 6"/>',
'check-circle'=>'<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
'heart'=>'<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/>',
'pin'=>'<path d="M19 10c0 6-7 11-7 11S5 16 5 10a7 7 0 0 1 14 0Z"/><circle cx="12" cy="10" r="2.5"/>',
'download'=>'<path d="M12 3v12m-5-5 5 5 5-5M4 16v5h16v-5"/>',
'help'=>'<circle cx="12" cy="12" r="9"/><path d="M9 8a3 3 0 0 1 6 0c0 2-3 2-3 5m0 3v1"/>',
'spark'=>'<path d="m12 3 2.5 6.5L21 12l-6.5 2.5L12 21l-2.5-6.5L3 12l6.5-2.5z"/>',
'list'=>'<path d="M9 6h12M9 12h12M9 18h12M3 6h1m-1 6h1m-1 6h1"/>',
];
@endphp
<svg {{ $attributes->merge(['class'=>'vi-icon '.$class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $paths[$name] ?? $paths['arrow'] !!}</svg>
