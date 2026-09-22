const toggle = document.querySelector('[data-nav-toggle]');
const nav = document.querySelector('[data-nav]');

if (toggle && nav) {
    toggle.addEventListener('click', () => {
        const open = nav.classList.toggle('open');
        toggle.setAttribute('aria-expanded', String(open));
    });

    nav.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            nav.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
        });
    });
}

const specialtySearch = document.querySelector('[data-specialty-search]');
if (specialtySearch) {
    specialtySearch.addEventListener('input', (event) => {
        const query = event.target.value.toLocaleLowerCase('es').trim();
        const items = [...document.querySelectorAll('[data-specialty-item]')];
        let visible = 0;
        items.forEach((item) => {
            const matches = item.textContent.toLocaleLowerCase('es').includes(query);
            item.hidden = !matches;
            if (matches) visible += 1;
        });
        const empty = document.querySelector('[data-specialty-empty]');
        if (empty) empty.hidden = visible > 0;
    });
}

document.querySelectorAll('[data-accordion] .accordion-item > button').forEach((button) => {
    button.addEventListener('click', () => {
        const item = button.closest('.accordion-item');
        const wasOpen = item.classList.contains('open');
        document.querySelectorAll('[data-accordion] .accordion-item').forEach((entry) => {
            entry.classList.remove('open');
            entry.querySelector('button').setAttribute('aria-expanded', 'false');
        });
        if (!wasOpen) {
            item.classList.add('open');
            button.setAttribute('aria-expanded', 'true');
        }
    });
});

document.querySelectorAll('[data-booking-demo] .choice-card').forEach((choice) => {
    choice.addEventListener('click', () => {
        document.querySelectorAll('[data-booking-demo] .choice-card').forEach((item) => item.classList.remove('selected'));
        choice.classList.add('selected');
    });
});


document.addEventListener('keydown',event=>{if(event.key==='Escape'){nav?.classList.remove('open');toggle?.setAttribute('aria-expanded','false');}});
