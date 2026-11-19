(() => {
    const carousel = document.querySelector('[data-home-carousel]');
    if (!carousel) return;

    const slides = [...carousel.querySelectorAll('[data-slide-name]')];
    const controls = carousel.querySelector('[data-carousel-controls]');
    const position = carousel.querySelector('[data-carousel-position]');
    const status = carousel.querySelector('[data-carousel-status]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const canHover = window.matchMedia('(any-hover: hover) and (any-pointer: fine)');
    const slideMotion = { duration: 540, easing: 'cubic-bezier(.35,.08,.25,1)' };
    let current = 0;
    let timer;
    let transitions = [];
    let glowFrame = 0;

    carousel.addEventListener('pointermove', event => {
        if (event.pointerType !== 'mouse' || reducedMotion.matches || !canHover.matches) return;
        window.cancelAnimationFrame(glowFrame);
        glowFrame = window.requestAnimationFrame(() => {
            const bounds = carousel.getBoundingClientRect();
            const x = (event.clientX - bounds.left) / bounds.width;
            const y = (event.clientY - bounds.top) / bounds.height;
            carousel.style.setProperty('--glow-x', `${Math.max(0, Math.min(100, x * 100))}%`);
            carousel.style.setProperty('--glow-y', `${Math.max(0, Math.min(100, y * 100))}%`);
            carousel.classList.add('has-pointer-glow');
        });
    });

    function hideGlow() {
        window.cancelAnimationFrame(glowFrame);
        carousel.classList.remove('has-pointer-glow');
    }

    carousel.addEventListener('pointerleave', hideGlow);
    canHover.addEventListener('change', hideGlow);

    function stopTransitions() {
        transitions.forEach(animation => animation.cancel());
        transitions = [];
    }

    function showSlide(offset, announce = false) {
        const animate = !reducedMotion.matches && typeof slides[current].animate === 'function';
        const start = animate ? slides.map(slide => getComputedStyle(slide).transform) : [];
        stopTransitions();
        const previous = current;
        current = (current + offset + slides.length) % slides.length;
        slides.forEach((slide, index) => {
            slide.dataset.depth = (index - current + slides.length) % slides.length;
            slide.classList.toggle('is-current', index === current);
        });

        if (animate) {
            const end = slides.map(slide => getComputedStyle(slide).transform);
            const turningCard = offset > 0 ? previous : current;
            transitions = slides.map((slide, index) => {
                if (index !== turningCard) {
                    return slide.animate([{ transform: start[index] }, { transform: end[index] }], slideMotion);
                }

                const fromLayer = offset > 0 ? 4 : 1;
                const toLayer = offset > 0 ? 1 : 4;
                const turn = 'translate(-22%, -10%) rotate(-13deg)';
                return slide.animate([
                    { transform: start[index], zIndex: fromLayer },
                    { zIndex: fromLayer, offset: .47 },
                    { transform: turn, zIndex: toLayer, offset: .48 },
                    { transform: end[index], zIndex: toLayer },
                ], slideMotion);
            });
        }

        position.textContent = `${current + 1} / ${slides.length}`;
        if (announce) status.textContent = `Image ${current + 1} sur ${slides.length} : ${slides[current].dataset.slideName}`;
    }

    function schedule() {
        window.clearTimeout(timer);
        if (reducedMotion.matches || document.hidden) return;
        timer = window.setTimeout(() => {
            if (reducedMotion.matches || document.hidden) return;
            showSlide(1);
            schedule();
        }, 3200);
    }

    function navigate(offset) {
        showSlide(offset, true);
        schedule();
    }

    carousel.querySelector('[data-carousel-previous]').addEventListener('click', () => navigate(-1));
    carousel.querySelector('[data-carousel-next]').addEventListener('click', () => navigate(1));
    reducedMotion.addEventListener('change', event => {
        if (event.matches) {
            stopTransitions();
            hideGlow();
        }
        schedule();
    });
    document.addEventListener('visibilitychange', schedule);

    controls.hidden = false;
    schedule();
})();
