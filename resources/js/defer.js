// Non-critical UI — učitava se odloženo (defer) posle glavnog site.js

function onDocumentReady(callback) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback);
    } else {
        callback();
    }
}

onDocumentReady(() => {
    initScrollReveal();
    initTestimonialsCarousel();
    initCategoryCatalog();
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
        const dropdown = catalog.querySelector('[data-category-dropdown]');
        const dropdownToggle = catalog.querySelector('[data-category-dropdown-toggle]');
        const dropdownMenu = catalog.querySelector('[data-category-dropdown-menu]');
        const dropdownLabel = catalog.querySelector('[data-category-dropdown-label]');

        if (tabs.length === 0) {
            return;
        }

        const setDropdownOpen = (isOpen) => {
            if (! dropdown || ! dropdownToggle || ! dropdownMenu) {
                return;
            }

            dropdown.dataset.open = isOpen ? 'true' : 'false';
            dropdownToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            dropdownMenu.hidden = ! isOpen;
        };

        const activeTabLabel = (slug) => {
            const activeTab = tabs.find((tab) => tab.dataset.categoryTab === slug);

            return activeTab?.textContent?.trim() ?? '';
        };

        const activate = (slug) => {
            tabs.forEach((tab) => {
                const isActive = tab.dataset.categoryTab === slug;

                tab.dataset.active = isActive ? 'true' : 'false';
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            panels.forEach((panel) => {
                panel.hidden = panel.dataset.categoryPanel !== slug;
            });

            if (dropdownLabel) {
                dropdownLabel.textContent = activeTabLabel(slug);
            }

            setDropdownOpen(false);

            const activePanel = panels.find((panel) => panel.dataset.categoryPanel === slug);

            if (activePanel?.dataset.pizzaSizePanel === 'true') {
                activatePizzaSize(
                    activePanel,
                    activePanel.dataset.activePizzaSize ?? '25',
                );
            }
        };

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                activate(tab.dataset.categoryTab);

                const url = new URL(window.location.href);
                url.hash = tab.dataset.categoryTab;
                history.replaceState(null, '', url);
            });
        });

        dropdownToggle?.addEventListener('click', (event) => {
            event.stopPropagation();
            setDropdownOpen(dropdownToggle.getAttribute('aria-expanded') !== 'true');
        });

        document.addEventListener('click', (event) => {
            if (dropdown && ! dropdown.contains(event.target)) {
                setDropdownOpen(false);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setDropdownOpen(false);
            }
        });

        window.matchMedia('(min-width: 1024px)').addEventListener('change', (event) => {
            if (event.matches) {
                setDropdownOpen(false);
            }
        });

        const hash = window.location.hash.replace(/^#/, '');
        const initialSlug = tabs.some((tab) => tab.dataset.categoryTab === hash)
            ? hash
            : tabs[0].dataset.categoryTab;

        activate(initialSlug);

        initPizzaSizeTabs(catalog);
    });
}

function activatePizzaSize(panel, sizeKey) {
    if (! panel || panel.dataset.pizzaSizePanel !== 'true') {
        return;
    }

    panel.dataset.activePizzaSize = sizeKey;

    const tabs = [...panel.querySelectorAll('[data-pizza-size-tab]')];
    const items = [...panel.querySelectorAll('[data-menu-item]')];
    const emptyMessage = panel.querySelector('[data-pizza-size-empty]');

    tabs.forEach((tab) => {
        const isActive = tab.dataset.pizzaSizeTab === sizeKey;

        tab.dataset.active = isActive ? 'true' : 'false';
        tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
    });

    let visibleCount = 0;

    items.forEach((item) => {
        const sizes = (item.dataset.sizes ?? '').split(',').filter(Boolean);
        const isVisible = sizes.includes(sizeKey);

        item.hidden = ! isVisible;

        if (isVisible) {
            visibleCount += 1;
        }
    });

    if (emptyMessage) {
        emptyMessage.hidden = visibleCount > 0;
    }
}

function initPizzaSizeTabs(catalog) {
    const panels = [...catalog.querySelectorAll('[data-pizza-size-panel]')];

    panels.forEach((panel) => {
        const tabs = [...panel.querySelectorAll('[data-pizza-size-tab]')];

        tabs.forEach((tab) => {
            tab.addEventListener('click', (event) => {
                event.preventDefault();
                activatePizzaSize(panel, tab.dataset.pizzaSizeTab);
            });
        });

        const defaultSize = panel.dataset.activePizzaSize ?? tabs[0]?.dataset.pizzaSizeTab ?? '25';

        activatePizzaSize(panel, defaultSize);
    });
}
