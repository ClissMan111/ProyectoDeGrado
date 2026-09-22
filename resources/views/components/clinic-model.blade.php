@props(['compact' => false])
<section {{ $attributes->class(['clinic-model', 'clinic-model-compact' => $compact]) }} data-clinic-model aria-label="Maqueta tridimensional interactiva del Centro de Salud Villa Israel">
    <div class="model-top"><span><i></i> EXPLORA EN 3D</span><span class="model-reference">Villa Israel · 360°</span></div>
    <div class="model-view-tabs" aria-label="Vistas del edificio"><button type="button" data-model-view="-28" aria-pressed="true">Fachada principal</button><button type="button" data-model-view="152" aria-pressed="false">Parte posterior</button></div>
    <div class="model-stage" data-model-stage tabindex="0" role="group" aria-label="Vista de la maqueta. Arrastra o usa las flechas izquierda y derecha para girar.">
        <div class="model-scene" data-model-scene aria-hidden="true">
            <div class="villa-ground"><div class="villa-yard"></div><div class="villa-sidewalk"></div><div class="villa-street"></div></div>
            <div class="villa-building">
                <div class="villa-wall villa-front">
                    <div class="villa-name">CENTRO DE SALUD<br>VILLA ISRAEL</div>
                    <i class="villa-window front-window-one"></i><i class="villa-window front-window-two curtained"></i><i class="villa-vent front-vent-one"></i><i class="villa-vent front-vent-two"></i><i class="villa-window front-window-three curtained"></i><i class="villa-window front-window-four curtained"></i>
                    <div class="villa-lower"><i class="villa-window barred lower-window"></i><i class="villa-entry"></i></div>
                </div>
                <div class="villa-wall villa-back">
                    <i class="villa-window rear-window-one"></i><i class="villa-vent rear-vent-one"></i><i class="villa-vent rear-vent-two"></i><i class="villa-window rear-window-two curtained"></i><i class="villa-window rear-window-three"></i>
                    <div class="villa-lower"><i class="rear-door"></i><i class="villa-vent rear-small-vent"></i><i class="villa-window barred rear-lower-window"></i><i class="villa-sink"></i></div>
                    <i class="villa-downpipe"></i>
                </div>
                <div class="villa-wall villa-left"><i class="villa-window side-window-one"></i><i class="villa-window side-window-two curtained"></i><div class="villa-lower"><i class="villa-window barred side-lower-window"></i></div></div>
                <div class="villa-wall villa-right"><i class="villa-window side-window-one curtained"></i><i class="villa-window side-window-two"></i><div class="villa-lower"><i class="villa-window barred side-lower-window"></i></div></div>
                <div class="villa-gable gable-front"></div><div class="villa-gable gable-back"></div>
                <div class="villa-roof roof-left"></div><div class="villa-roof roof-right"></div><div class="villa-ridge"></div>
            </div>
            <div class="villa-fence"><i class="fence-column column-one"></i><i class="fence-column column-two"></i><i class="fence-column column-three"></i><i class="fence-column column-four"></i><i class="fence-column column-five"></i><i class="fence-column column-six"></i><div class="fence-panel panel-one"></div><div class="fence-panel panel-two"></div><div class="fence-panel panel-three"></div><div class="fence-panel panel-four"></div><div class="fence-grille"></div><div class="fence-gate"></div></div>
            <div class="villa-awning front-awning"></div>
            <div class="villa-side-fence"><i></i><i></i><i></i></div><div class="villa-awning side-awning"></div>
            <div class="villa-rear-porch"><i></i><i></i><i></i></div><div class="villa-awning rear-awning"></div>
            <div class="villa-annex"><div class="annex-face annex-back"><i class="villa-vent"></i><i class="villa-window barred annex-window-one"></i><i class="villa-window barred annex-window-two"></i><i class="annex-door"></i></div><div class="annex-face annex-left"></div><div class="annex-face annex-right"></div><div class="annex-roof"></div></div>
            <div class="villa-tank">@for($side=0; $side<16; $side++)<i class="tank-side" style="--tank-side:{{ $side }}"></i>@endfor<i class="tank-top"></i></div>
        </div>
        <span class="model-hint"><x-icon name="spark"/> Arrastra para descubrir cada lado</span>
    </div>
    <div class="model-controls"><button type="button" data-model-turn="-25" aria-label="Girar maqueta a la izquierda"><x-icon name="chevron" class="model-left-arrow"/></button><label>Girar <input type="range" min="-180" max="180" value="-28" aria-label="Ángulo de la maqueta" data-model-angle></label><button type="button" data-model-turn="25" aria-label="Girar maqueta a la derecha"><x-icon name="chevron"/></button><button type="button" data-model-reset aria-label="Restablecer vista 3D">Restablecer</button></div>
</section>
