/* ShopNow: optional enhancements; core shopping works without JavaScript. */
document.addEventListener('DOMContentLoaded', () => {
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (!reduceMotion && 'IntersectionObserver' in window) {
    const elements = document.querySelectorAll('.hero, .product, .panel, .chips, .stats');
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.08, rootMargin: '0px 0px 30px 0px' });
    elements.forEach(el => { el.classList.add('reveal-on-scroll'); observer.observe(el); });
  }
  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', () => {
      const button = form.querySelector('button[type="submit"],button:not([type])');
      if (button && !button.disabled) {
        button.setAttribute('aria-busy', 'true');
      }
    });
  });
});
