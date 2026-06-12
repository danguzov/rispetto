// Public site scripts

document.addEventListener('DOMContentLoaded', () => {
    initMobileNav();
    initTestimonialsCarousel();
    initCategoryCatalog();
});

function initMobileNav() {
    const toggle = document.querySelector('[data-mobile-nav-toggle]');
    const panel = document.querySelector('[data-mobile-nav-panel]');

    if (! toggle || ! panel) {
        return;
    }

    toggle.addEventListener('click', () => {
        const isOpen = panel.classList.toggle('hidden') === false;
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
}

function initTestimonialsCarousel() {
    const root = document.querySelector('[data-testimonials]');

    if (! root) {
        return;
    }

    const track = root.querySelector('[data-testimonials-track]');
    const slides = [...root.querySelectorAll('[data-testimonials-slide]')];
    const dots = [...root.querySelectorAll('[data-testimonials-dot]')];

    if (! track || slides.length === 0) {
        return;
    }

    let activeIndex = 0;

    const visibleCount = () => (window.matchMedia('(min-width: 768px)').matches ? 3 : 1);

    const maxIndex = () => Math.max(0, slides.length - visibleCount());

    const update = () => {
        const index = Math.min(activeIndex, maxIndex());
        activeIndex = index;

        const gap = Number.parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap) || 24;
        const slideWidth = slides[0].getBoundingClientRect().width;
        track.style.transform = `translateX(-${index * (slideWidth + gap)}px)`;

        dots.forEach((dot, dotIndex) => {
            const isActive = dotIndex === index;
            dot.dataset.active = isActive ? 'true' : 'false';
            dot.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });
    };

    dots.forEach((dot) => {
        dot.addEventListener('click', () => {
            activeIndex = Number(dot.dataset.testimonialsDot ?? 0);
            update();
        });
    });

    window.addEventListener('resize', update);

    update();
}

function initCategoryCatalog() {
    const catalogs = document.querySelectorAll('[data-category-catalog]');

    catalogs.forEach((catalog) => {
        const tabs = [...catalog.querySelectorAll('[data-category-tab]')];
        const panels = [...catalog.querySelectorAll('[data-category-panel]')];

        if (tabs.length === 0) {
            return;
        }

        const activate = (slug) => {
            tabs.forEach((tab) => {
                const isActive = tab.dataset.categoryTab === slug;

                tab.dataset.active = isActive ? 'true' : 'false';
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            panels.forEach((panel) => {
                panel.hidden = panel.dataset.categoryPanel !== slug;
            });
        };

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                activate(tab.dataset.categoryTab);

                const url = new URL(window.location.href);
                url.hash = tab.dataset.categoryTab;
                history.replaceState(null, '', url);
            });
        });

        const hash = window.location.hash.replace(/^#/, '');
        const initialSlug = tabs.some((tab) => tab.dataset.categoryTab === hash)
            ? hash
            : tabs[0].dataset.categoryTab;

        activate(initialSlug);
    });
}
