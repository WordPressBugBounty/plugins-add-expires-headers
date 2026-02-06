document.addEventListener("DOMContentLoaded", function () {
    const lazyBlocks = document.querySelectorAll('.lazy-shortcode, .lazy-widget');

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const content = atob(el.getAttribute('data-content'));
                el.innerHTML = content;
                el.classList.add('lazyloaded');
                obs.unobserve(el);
            }
        });
    }, { threshold: 0.1 });

    lazyBlocks.forEach(block => observer.observe(block));
});