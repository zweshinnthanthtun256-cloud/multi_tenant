import 'bootstrap/dist/js/bootstrap.bundle.min.js';
document.addEventListener('DOMContentLoaded',()=>{
    const toggle=document.querySelector('[data-nav-toggle]');
    const nav=document.querySelector('.sidebar');
    toggle?.addEventListener('click',()=>{const open=nav.classList.toggle('open');toggle.setAttribute('aria-expanded',String(open));});
    document.addEventListener('keydown',e=>{if(e.key==='Escape'){nav?.classList.remove('open');toggle?.setAttribute('aria-expanded','false');}});
    document.querySelectorAll('form[data-confirm]').forEach(form=>form.addEventListener('submit',event=>{
        if(!window.confirm(form.dataset.confirm))event.preventDefault();
    }));
});
