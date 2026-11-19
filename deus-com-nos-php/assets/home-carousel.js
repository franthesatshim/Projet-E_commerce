(() => {
    const carousel = document.querySelector('[data-home-carousel]');
    if (!carousel) return;

    const slides = [...carousel.querySelectorAll('[data-slide-name]')];
    const controls = carousel.querySelector('[data-carousel-controls]');
    const position = carousel.querySelector('[data-carousel-position]');
    const toggle = carousel.querySelector('[data-carousel-toggle]');
    const status = carousel.querySelector('[data-carousel-status]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const slideMotion = { duration: 680, easing: 'cubic-bezier(.25,.7,.25,1)' };
    let current = 0;
    let playing = !reducedMotion.matches;
    let hovering = false;
    let focused = false;
    let resumedOnControls = false;
    let timer;
    let transitions = [];

    function stopTransitions() {
        transitions.forEach(animation => animation.cancel());
        transitions = [];
    }

    function showSlide(offset, announce = false) {
        stopTransitions();
        const outgoing = slides[current];
        outgoing.classList.remove('is-current');
        current = (current + offset + slides.length) % slides.length;
        const incoming = slides[current];
        incoming.classList.add('is-current');

        if (!reducedMotion.matches && typeof incoming.animate === 'function') {
            const distance = offset > 0 ? 100 : -100;
            transitions = [
                outgoing.animate([
                    { transform: 'translateX(0)', opacity: 1 },
                    { transform: `translateX(${-distance}%)`, opacity: 1 },
                ], slideMotion),
                incoming.animate([
                    { transform: `translateX(${distance}%)` },
                    { transform: 'translateX(0)' },
                ], slideMotion),
            ];
        }

        position.textContent = `${current + 1} / ${slides.length}`;
        if (announce) status.textContent = `Image ${current + 1} sur ${slides.length} : ${incoming.dataset.slideName}`;
    }

    function schedule() {
        window.clearTimeout(timer);
        if (!playing || document.hidden || (!resumedOnControls && (hovering || focused))) return;
        timer = window.setTimeout(() => {
            showSlide(1);
            schedule();
        }, 5500);
    }

    function updatePlayback() {
        toggle.textContent = playing ? 'Pause' : 'Reprendre';
        toggle.setAttribute('aria-label', playing ? 'Mettre le défilement en pause' : 'Reprendre le défilement');
        schedule();
    }

    function navigate(offset) {
        playing = false;
        showSlide(offset, true);
        updatePlayback();
    }

    carousel.querySelector('[data-carousel-previous]').addEventListener('click', () => navigate(-1));
    carousel.querySelector('[data-carousel-next]').addEventListener('click', () => navigate(1));
    toggle.addEventListener('click', () => {
        playing = !playing;
        resumedOnControls = playing;
        updatePlayback();
    });

    carousel.addEventListener('pointerenter', event => {
        if (event.pointerType !== 'mouse') return;
        hovering = true;
        schedule();
    });
    carousel.addEventListener('pointerleave', event => {
        if (event.pointerType !== 'mouse') return;
        hovering = false;
        resumedOnControls = false;
        schedule();
    });
    carousel.addEventListener('focusin', () => {
        focused = true;
        resumedOnControls = false;
        schedule();
    });
    carousel.addEventListener('focusout', event => {
        if (carousel.contains(event.relatedTarget)) return;
        focused = false;
        resumedOnControls = false;
        schedule();
    });
    reducedMotion.addEventListener('change', event => {
        if (event.matches) {
            stopTransitions();
            playing = false;
            updatePlayback();
        }
    });
    document.addEventListener('visibilitychange', schedule);

    carousel.classList.add('has-carousel');
    controls.hidden = false;
    updatePlayback();
})();
