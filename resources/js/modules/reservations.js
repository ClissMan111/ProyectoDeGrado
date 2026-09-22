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
