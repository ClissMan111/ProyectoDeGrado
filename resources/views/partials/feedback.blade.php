@if(session('success'))<div class="notice success" role="status"><span>✓</span><div>{{ session('success') }}</div><button type="button" data-dismiss aria-label="Cerrar mensaje">×</button></div>@endif
@if($errors->any())<div class="notice error" role="alert"><div><strong>Revisa estos datos</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>@endif
