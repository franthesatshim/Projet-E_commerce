(() => {
    if (!('IntersectionObserver' in window)) return;

    const items = document.querySelectorAll('[data-scroll-reveal]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const observer = new IntersectionObserver(entries => {
        if (reducedMotion.matches) return;
        const staggerByParent = new Map();

        entries.forEach(({ target, isIntersecting }) => {
            if (!isIntersecting) {
                target.classList.remove('is-revealing');
                return;
            }

            const group = target.parentElement;
            const previous = staggerByParent.get(group) ?? 0;
            target.style.setProperty('--reveal-delay', `${Math.min(previous * 45, 135)}ms`);
            target.classList.add('is-revealing');
            staggerByParent.set(group, previous + 1);
        });
    }, { rootMargin: '24px 0px' });

    function updateMotion() {
        observer.disconnect();
        items.forEach(item => {
            item.classList.remove('is-revealing');
            if (!reducedMotion.matches) observer.observe(item);
        });
    }

    reducedMotion.addEventListener('change', updateMotion);
    updateMotion();
})();
