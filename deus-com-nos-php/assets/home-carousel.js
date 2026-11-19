(() => {
    const carousel = document.querySelector('[data-home-carousel]');
    if (!carousel) return;

    const slides = [...carousel.querySelectorAll('[data-slide-name]')];
    const controls = carousel.querySelector('[data-carousel-controls]');
    const position = carousel.querySelector('[data-carousel-position]');
    const toggle = carousel.querySelector('[data-carousel-toggle]');
    const status = carousel.querySelector('[data-carousel-status]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    let current = 0;
    let playing = !reducedMotion.matches;
    let hovering = false;
    let focused = false;
    let resumedOnControls = false;
    let timer;

    function showSlide(offset, announce = false) {
        slides[current].classList.remove('is-current');
        current = (current + offset + slides.length) % slides.length;
        slides[current].classList.add('is-current');
        position.textContent = `${current + 1} / ${slides.length}`;
        if (announce) status.textContent = `Image ${current + 1} sur ${slides.length} : ${slides[current].dataset.slideName}`;
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
            playing = false;
            updatePlayback();
        }
    });
    document.addEventListener('visibilitychange', schedule);

    carousel.classList.add('has-carousel');
    controls.hidden = false;
    updatePlayback();
})();
