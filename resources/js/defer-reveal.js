import { onDocumentReady } from './site/on-ready.js';

onDocumentReady(() => {
    initScrollReveal();
});

function initScrollReveal() {
    const sections = document.querySelectorAll('[data-site-reveal]');

    if (sections.length === 0) {
        return;
    }

    const reveal = (section) => {
        if (section.dataset.revealed === 'true') {
            return;
        }

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                section.dataset.revealed = 'true';
            });
        });
    };

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        sections.forEach((section) => {
            section.dataset.revealed = 'true';
        });

        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (! entry.isIntersecting) {
                    return;
                }

                reveal(entry.target);
                observer.unobserve(entry.target);
            });
        },
        {
            root: null,
            rootMargin: '0px 0px -4% 0px',
            threshold: 0.08,
        },
    );

    sections.forEach((section) => {
        section.getBoundingClientRect();
        observer.observe(section);
    });
}
