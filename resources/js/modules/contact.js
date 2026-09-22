document.querySelectorAll('[data-copy-address]').forEach(button => {
    button.addEventListener('click', async () => {
        const status = button.parentElement.querySelector('[data-copy-status]');
        try {
            await navigator.clipboard.writeText(button.dataset.copyAddress);
            status.textContent = 'Dirección copiada.';
        } catch {
            status.textContent = 'Puedes seleccionar y copiar la dirección que aparece arriba.';
        }
    });
});
