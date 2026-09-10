import './bootstrap';
import '../css/refinements.css';

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

const dashboardToggle = document.querySelector('[data-dashboard-toggle]');
const dashboardNav = document.querySelector('[data-dashboard-nav]');
if (dashboardToggle && dashboardNav) {
    const mobile = matchMedia('(max-width: 820px)');
    const backdrop = document.querySelector('.dashboard-backdrop');
    const setMenu = (open, restoreFocus = false) => {
        dashboardNav.classList.toggle('open', open);
        dashboardToggle.setAttribute('aria-expanded', String(open));
        dashboardNav.inert = mobile.matches && !open;
        backdrop.hidden = !mobile.matches || !open;
        if (restoreFocus) dashboardToggle.focus();
    };
    dashboardToggle.addEventListener('click', () => {
        setMenu(!dashboardNav.classList.contains('open'));
    });
    document.querySelectorAll('[data-dashboard-close]').forEach(button => button.addEventListener('click', () => setMenu(false, true)));
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && dashboardNav.classList.contains('open')) setMenu(false, true); });
    mobile.addEventListener('change', () => setMenu(false));
    setMenu(false);
}

document.querySelectorAll('[data-password-toggle]').forEach(button => button.addEventListener('click', () => {
    const input=button.parentElement.querySelector('input');
    const show=input.type==='password'; input.type=show?'text':'password'; button.textContent=show?'Ocultar':'Ver';
    button.setAttribute('aria-label',show?'Ocultar contraseña':'Mostrar contraseña');
}));
document.querySelectorAll('[data-dismiss]').forEach(button=>button.addEventListener('click',()=>button.closest('.notice').remove()));
document.addEventListener('keydown',event=>{if(event.key==='Escape'){dashboardNav?.classList.remove('open');dashboardToggle?.setAttribute('aria-expanded','false');nav?.classList.remove('open');toggle?.setAttribute('aria-expanded','false');}});
const header=document.querySelector('[data-header]');
window.addEventListener('scroll',()=>header?.classList.toggle('scrolled',window.scrollY>15),{passive:true});
if ('IntersectionObserver' in window && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const observer=new IntersectionObserver(entries=>entries.forEach(entry=>{if(entry.isIntersecting){entry.target.classList.add('visible');observer.unobserve(entry.target);}}),{threshold:.08});
    document.querySelectorAll('.specialty-card,.catalog-card,.process-list li,.center-copy,.contact-cards article').forEach(el=>{el.classList.add('reveal-ready');observer.observe(el);});
}
document.querySelectorAll('form[data-confirm-message]').forEach(form=>form.addEventListener('submit',event=>{
    if(form.dataset.confirmed==='yes') return;
    event.preventDefault();
    const dialog=document.createElement('dialog');dialog.className='confirm-dialog';
    const title=document.createElement('h2');title.textContent='Confirmar acción';
    const message=document.createElement('p');message.textContent=form.dataset.confirmMessage;
    const actions=document.createElement('div');actions.className='form-footer';
    const cancel=document.createElement('button');cancel.type='button';cancel.className='button ghost-button';cancel.textContent='Volver';
    const confirm=document.createElement('button');confirm.type='button';confirm.className='button button-dark';confirm.textContent='Confirmar';
    cancel.onclick=()=>dialog.close();confirm.onclick=()=>{form.dataset.confirmed='yes';dialog.close();form.requestSubmit();};
    actions.append(cancel,confirm);dialog.append(title,message,actions);document.body.append(dialog);dialog.addEventListener('close',()=>dialog.remove());dialog.showModal();cancel.focus();
}));

const reservation=document.querySelector('[data-reservation]');
if(reservation){
    const specialty=reservation.querySelector('[data-specialty]'),date=reservation.querySelector('[data-date]'),results=reservation.querySelector('[data-slots]'),summary=reservation.querySelector('[data-summary]'),confirm=reservation.querySelector('[data-confirm]');
    let requestController;
    const reset=()=>{reservation.querySelector('[data-medico]').value='';reservation.querySelector('[data-time]').value='';confirm.disabled=true;summary.hidden=true;};
    const load=async()=>{
        requestController?.abort();reset();results.replaceChildren();
        if(!specialty.value || !date.value){results.textContent='Selecciona una especialidad y fecha.';return;}
        const controller=new AbortController();requestController=controller;
        results.textContent='Consultando horarios…';results.setAttribute('aria-busy','true');
        const params=new URLSearchParams({especialidad_id:specialty.value,fecha:date.value});if(reservation.dataset.cita)params.set('cita_id',reservation.dataset.cita);
        try{
            const response=await fetch(reservation.dataset.url+'?'+params,{signal:controller.signal,headers:{Accept:'application/json'}});
            if(!response.ok)throw new Error('No pudimos consultar los horarios. Revisa la fecha o vuelve a iniciar sesión.');
            const data=await response.json();results.replaceChildren();let total=0;
            data.medicos.forEach(medico=>{
                const card=document.createElement('section');card.className='slot-doctor';const heading=document.createElement('h3');heading.textContent=medico.nombre;card.append(heading);
                const slots=document.createElement('div');slots.className='slot-buttons';
                medico.horarios.forEach(slot=>{
                    total++;const button=document.createElement('button');button.type='button';button.textContent=slot.inicio+' – '+slot.fin;button.setAttribute('aria-pressed','false');button.setAttribute('aria-label',medico.nombre+', '+button.textContent);
                    button.onclick=()=>{
                        results.querySelectorAll('button').forEach(b=>b.setAttribute('aria-pressed','false'));button.setAttribute('aria-pressed','true');
                        reservation.querySelector('[data-medico]').value=medico.id;reservation.querySelector('[data-time]').value=slot.inicio;
                        summary.textContent='Revisa tu reserva: '+specialty.selectedOptions[0].text+' · '+medico.nombre+' · '+date.value.split('-').reverse().join('/')+' · '+slot.inicio+' a '+slot.fin+'.';summary.hidden=false;confirm.disabled=false;
                    };slots.append(button);
                });
                if(!medico.horarios.length){const note=document.createElement('p');note.className='form-hint';note.textContent='Sin horarios libres para esta fecha.';slots.append(note);}card.append(slots);results.append(card);
            });
            if(!total){const empty=document.createElement('p');empty.className='empty-state';empty.textContent='No hay horarios disponibles. Prueba con otra fecha o especialidad.';results.append(empty);}
        }catch(error){if(error.name!=='AbortError'){results.textContent=error.message;const retry=document.createElement('button');retry.type='button';retry.className='button ghost-button';retry.textContent='Reintentar';retry.onclick=load;results.append(retry);}}
        finally{if(requestController===controller)results.removeAttribute('aria-busy');}
    };
    specialty.addEventListener('change',load);date.addEventListener('change',load);load();
    reservation.addEventListener('submit',()=>{confirm.disabled=true;confirm.textContent='Guardando tu cita…';});
}
