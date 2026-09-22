// Presentation effects only: no reservation, authentication or data behavior.
const motionPreference = matchMedia('(prefers-reduced-motion: reduce)');
const progress = document.querySelector('[data-reading-progress]');
const backTop = document.querySelector('[data-back-top]');
let scrollFrame = 0;
const paintScroll = () => {
    scrollFrame = 0;
    const distance = document.documentElement.scrollHeight - innerHeight;
    const ratio = distance > 0 ? Math.min(1, Math.max(0, scrollY / distance)) : 0;
    progress?.style.setProperty('--reading-progress', ratio);
    if (backTop) {
        const visible = scrollY > 600;
        backTop.setAttribute('aria-hidden', String(!visible));
        backTop.tabIndex = visible ? 0 : -1;
    }
};
const scheduleScroll = () => { if (!scrollFrame) scrollFrame = requestAnimationFrame(paintScroll); };
window.addEventListener('scroll', scheduleScroll, { passive: true });
window.addEventListener('resize', scheduleScroll, { passive: true });
window.addEventListener('load', scheduleScroll, { once: true });
paintScroll();
backTop?.addEventListener('click', () => {
    const content = document.getElementById('contenido');
    if (content) { content.tabIndex = -1; content.focus({ preventScroll: true }); }
    window.scrollTo({ top: 0, behavior: motionPreference.matches ? 'instant' : 'smooth' });
});

const animateOnce = (element, className) => {
    const finish = event => {
        if (event.target !== element) return;
        element.classList.remove(className);
        element.style.removeProperty('animation-delay');
        element.removeEventListener('animationend', finish);
    };
    element.addEventListener('animationend', finish);
    element.classList.add(className);
};
const arrivals = document.querySelectorAll('.hero-copy,.hero .booking-card,.page-hero-grid,.auth-message,.auth-card,.register-layout>aside,.register-card');
const revealTargets = document.querySelectorAll('.specialty-card,.catalog-card,.process-list li,.center-copy,.contact-cards article,.guide-steps article,.value-grid article,.vi-stat,.vi-secondary-column>section,.report-number');
if (!motionPreference.matches) {
    arrivals.forEach((element, index) => {
        element.style.animationDelay = Math.min(index * 60, 180) + 'ms';
        animateOnce(element, 'ui-arrival');
    });
}
// Content stays visible even before observation, without JavaScript or on failure.
if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            if (!motionPreference.matches) animateOnce(entry.target, 'ui-revealed');
            observer.unobserve(entry.target);
        });
    }, { threshold: .12 });
    revealTargets.forEach(element => observer.observe(element));
}

const pointerPreference = matchMedia('(hover: hover) and (pointer: fine)');
document.querySelectorAll('.specialty-card:not(.featured),.catalog-card,.vi-stat:not(.vi-stat-featured),.contact-cards article').forEach(card => {
    card.classList.add('ui-spotlight');
    let pointerFrame = 0;
    card.addEventListener('pointermove', event => {
        if (motionPreference.matches || !pointerPreference.matches || pointerFrame) return;
        pointerFrame = requestAnimationFrame(() => {
            const bounds = card.getBoundingClientRect();
            card.style.setProperty('--light-x', (event.clientX - bounds.left) + 'px');
            card.style.setProperty('--light-y', (event.clientY - bounds.top) + 'px');
            pointerFrame = 0;
        });
    }, { passive: true });
});

document.addEventListener('click', event => {
    if (motionPreference.matches) return;
    const button = event.target.closest('.button,.vi-button');
    if (!button || button.disabled || !button.animate) return;
    button.querySelector('.ui-press-wave')?.remove();
    const bounds = button.getBoundingClientRect();
    const size = Math.max(bounds.width, bounds.height) * 2;
    const wave = document.createElement('span');
    wave.className = 'ui-press-wave';
    wave.setAttribute('aria-hidden', 'true');
    wave.style.setProperty('--wave-size', size + 'px');
    wave.style.setProperty('--wave-x', ((event.detail ? event.clientX - bounds.left : bounds.width / 2) - size / 2) + 'px');
    wave.style.setProperty('--wave-y', ((event.detail ? event.clientY - bounds.top : bounds.height / 2) - size / 2) + 'px');
    button.append(wave);
    wave.animate([{ transform: 'scale(0)', opacity: .18 }, { transform: 'scale(1)', opacity: 0 }], { duration: 450, easing: 'ease-out' }).finished.catch(() => {}).finally(() => wave.remove());
});

motionPreference.addEventListener('change', () => {
    if (!motionPreference.matches) return;
    document.querySelectorAll('.ui-arrival,.ui-revealed').forEach(element => element.classList.remove('ui-arrival', 'ui-revealed'));
    document.querySelectorAll('.ui-press-wave').forEach(element => { element.getAnimations().forEach(animation => animation.cancel()); element.remove(); });
});
