document.querySelectorAll('[data-password-toggle]').forEach(button => button.addEventListener('click', () => {
    const input=button.parentElement.querySelector('input');
    const show=input.type==='password'; input.type=show?'text':'password'; button.textContent=show?'Ocultar':'Ver';
    button.setAttribute('aria-label',show?'Ocultar contraseña':'Mostrar contraseña');
}));
document.querySelectorAll('[data-dismiss]').forEach(button=>button.addEventListener('click',()=>button.closest('.notice').remove()));

const header=document.querySelector('[data-header]');
window.addEventListener('scroll',()=>header?.classList.toggle('scrolled',window.scrollY>15),{passive:true});
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

