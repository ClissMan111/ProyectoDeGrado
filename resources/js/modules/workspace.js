const workspace = document.querySelector('[data-workspace]');
if (workspace) {
    const storage = {
        get(key, fallback = null) { try { return JSON.parse(localStorage.getItem(key)) ?? fallback; } catch { return fallback; } },
        set(key, value) { try { localStorage.setItem(key, JSON.stringify(value)); } catch { /* Preferences are optional. */ } },
    };
    const sidebar = document.querySelector('[data-dashboard-nav]');
    const main = document.querySelector('[data-dashboard-main]');
    const toggle = document.querySelector('[data-dashboard-toggle]');
    const backdrop = document.querySelector('.dashboard-backdrop');
    const compact = document.querySelector('[data-sidebar-collapse]');
    const mobile = matchMedia('(max-width: 820px)');
    const setCompact = (value) => {
        workspace.classList.toggle('sidebar-compact', value);
        compact.setAttribute('aria-expanded', String(!value));
        compact.setAttribute('aria-label', value ? 'Ampliar navegación' : 'Reducir navegación');
        compact.title = value ? 'Ampliar navegación' : 'Reducir navegación';
    };
    setCompact(storage.get('vi.sidebar.compact', false) === true);
    compact.addEventListener('click', () => {
        const value = !workspace.classList.contains('sidebar-compact');
        setCompact(value); storage.set('vi.sidebar.compact', value);
    });
    const setMenu = (open, restoreFocus = false) => {
        const visible = mobile.matches && open;
        sidebar.classList.toggle('open', visible);
        sidebar.inert = mobile.matches && !visible;
        main.inert = visible;
        backdrop.hidden = !visible;
        toggle.setAttribute('aria-expanded', String(visible));
        document.body.style.overflow = visible ? 'hidden' : '';
        if (visible) sidebar.querySelector('[data-dashboard-close]').focus();
        else if (restoreFocus) toggle.focus();
    };
    toggle.addEventListener('click', () => setMenu(!sidebar.classList.contains('open')));
    document.querySelectorAll('[data-dashboard-close]').forEach(button => button.addEventListener('click', () => setMenu(false, true)));
    mobile.addEventListener('change', () => setMenu(false));
    sidebar.addEventListener('keydown', event => {
        if (!mobile.matches || !sidebar.classList.contains('open')) return;
        if (event.key === 'Escape') { event.preventDefault(); setMenu(false, true); }
        if (event.key !== 'Tab') return;
        const focusable = [...sidebar.querySelectorAll('a,button')].filter(el => el.getClientRects().length);
        const first = focusable[0], last = focusable.at(-1);
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    });
    setMenu(false);

    const dialog = document.querySelector('[data-command-dialog]');
    const search = document.querySelector('[data-command-search]');
    const results = document.querySelector('[data-command-results]');
    const empty = document.querySelector('[data-command-empty]');
    const links = [...sidebar.querySelectorAll('[data-command]')].map(link => ({ href: link.href, text: link.querySelector('.vi-nav-text').textContent, icon: link.querySelector('svg') }));
    const normalize = value => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('es').trim();
    let selected = 0;
    const highlight = () => results.querySelectorAll('a').forEach((link, index) => link.classList.toggle('active', index === selected));
    const render = () => {
        results.replaceChildren(); selected = 0;
        links.filter(link => normalize(link.text).includes(normalize(search.value))).forEach(link => {
            const a = document.createElement('a'); a.href = link.href;
            a.append(link.icon.cloneNode(true), document.createTextNode(link.text));
            a.addEventListener('focus', () => { selected = [...results.children].indexOf(a); highlight(); });
            results.append(a);
        });
        empty.hidden = results.children.length > 0; highlight();
    };
    const openCommands = () => {
        setMenu(false);
        if (!dialog.open) dialog.showModal();
        search.value = ''; render(); search.focus();
    };
    document.querySelector('[data-command-open]').addEventListener('click', openCommands);
    document.querySelector('[data-command-close]').addEventListener('click', () => dialog.close());
    search.addEventListener('input', render);
    dialog.addEventListener('click', event => {
        const bounds = dialog.getBoundingClientRect();
        if (event.target === dialog && (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom)) dialog.close();
    });
    dialog.addEventListener('keydown', event => {
        const choices = [...results.children];
        if (!choices.length) return;
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            if (document.activeElement === search) selected = event.key === 'ArrowDown' ? 0 : choices.length - 1;
            else selected = (selected + (event.key === 'ArrowDown' ? 1 : -1) + choices.length) % choices.length;
            highlight(); choices[selected].focus();
        } else if (event.key === 'Enter' && document.activeElement === search) {
            event.preventDefault(); choices[selected]?.click();
        }
    });
    document.addEventListener('keydown', event => {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') { event.preventDefault(); openCommands(); }
    });
    const profile = document.querySelector('.vi-profile-menu');
    document.addEventListener('click', event => { if (!profile.contains(event.target)) profile.open = false; });
    profile.addEventListener('keydown', event => { if (event.key === 'Escape') { profile.open = false; profile.querySelector('summary').focus(); } });

    document.querySelectorAll('[data-agenda]').forEach(agenda => {
        const button = agenda.querySelector('[data-agenda-density]');
        const setDensity = value => { agenda.classList.toggle('is-compact', value); button.setAttribute('aria-pressed', String(value)); button.setAttribute('aria-label', value ? 'Activar vista cómoda' : 'Activar vista compacta'); };
        setDensity(storage.get('vi.agenda.compact', false) === true);
        button.addEventListener('click', () => { const value = !agenda.classList.contains('is-compact'); setDensity(value); storage.set('vi.agenda.compact', value); });
    });
    document.querySelectorAll('[data-visit-checklist]').forEach(list => {
        const key = 'vi.visit.' + list.dataset.visitChecklist;
        const inputs = [...list.querySelectorAll('[data-visit-check]')];
        const saved = storage.get(key, []);
        inputs.forEach(input => { input.checked = Array.isArray(saved) && saved.includes(input.dataset.visitCheck); });
        const update = () => { const checked = inputs.filter(input => input.checked); list.querySelector('[data-check-progress]').textContent = checked.length + '/' + inputs.length; storage.set(key, checked.map(input => input.dataset.visitCheck)); };
        inputs.forEach(input => input.addEventListener('change', update)); update();
    });
}
