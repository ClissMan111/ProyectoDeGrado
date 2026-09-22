// Lightweight CSS 3D scene. All controls work by keyboard, pointer and touch.
document.querySelectorAll('[data-clinic-model]').forEach(model => {
    const stage = model.querySelector('[data-model-stage]');
    const scene = model.querySelector('[data-model-scene]');
    const range = model.querySelector('[data-model-angle]');
    let angle = -28;
    let drag = null;
    const turn = value => {
        angle = Math.round(((value + 180) % 360 + 360) % 360 - 180);
        scene.style.setProperty('--model-angle', angle + 'deg');
        range.value = angle;
        range.setAttribute('aria-valuetext', angle + ' grados');
        model.querySelectorAll('[data-model-view]').forEach(button => {
            button.setAttribute('aria-pressed', String(angle === Number(button.dataset.modelView)));
        });
    };
    range.addEventListener('input', () => turn(Number(range.value)));
    model.querySelectorAll('[data-model-turn]').forEach(button => button.addEventListener('click', () => turn(angle + Number(button.dataset.modelTurn))));
    model.querySelector('[data-model-reset]').addEventListener('click', () => turn(-28));
    model.querySelectorAll('[data-model-view]').forEach(button => button.addEventListener('click', () => turn(Number(button.dataset.modelView))));
    stage.addEventListener('keydown', event => {
        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
            event.preventDefault(); turn(angle + (event.key === 'ArrowLeft' ? -12 : 12));
        } else if (event.key === 'Home') { event.preventDefault(); turn(-28); }
    });
    stage.addEventListener('pointerdown', event => {
        if (!event.isPrimary || event.button !== 0) return;
        drag = { id: event.pointerId, x: event.clientX, y: event.clientY, angle, active: false };
    });
    stage.addEventListener('pointermove', event => {
        if (!drag || drag.id !== event.pointerId) return;
        const dx = event.clientX - drag.x, dy = event.clientY - drag.y;
        if (!drag.active && Math.abs(dx) > 8 && Math.abs(dx) > Math.abs(dy)) {
            drag.active = true; stage.setPointerCapture(event.pointerId); stage.classList.add('is-dragging');
        }
        if (drag.active) { event.preventDefault(); turn(drag.angle + dx * .55); }
    });
    const release = event => {
        if (!drag || drag.id !== event.pointerId) return;
        if (stage.hasPointerCapture(event.pointerId)) stage.releasePointerCapture(event.pointerId);
        stage.classList.remove('is-dragging'); drag = null;
    };
    stage.addEventListener('pointerup', release);
    stage.addEventListener('pointercancel', release);
    stage.addEventListener('pointerleave', event => { if (!drag?.active) release(event); });
    turn(angle);
});
