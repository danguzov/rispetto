// Kritični skriptovi sajta (navigacija)

document.addEventListener('DOMContentLoaded', () => {
    initMobileNav();
});

function initMobileNav() {
    const root = document.querySelector('[data-mobile-nav]');
    const toggle = document.querySelector('[data-mobile-nav-toggle]');
    const panel = document.querySelector('[data-mobile-nav-panel]');
    const backdrop = document.querySelector('[data-mobile-nav-backdrop]');
    const closeButtons = document.querySelectorAll('[data-mobile-nav-close]');
    const toggleLabel = document.querySelector('[data-mobile-nav-toggle-label]');
    const iconOpen = document.querySelector('[data-mobile-nav-icon-open]');
    const iconClose = document.querySelector('[data-mobile-nav-icon-close]');

    if (! root || ! toggle || ! panel) {
        return;
    }

    const setOpen = (isOpen) => {
        root.dataset.open = isOpen ? 'true' : 'false';
        root.toggleAttribute('inert', ! isOpen);
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        document.body.classList.toggle('overflow-hidden', isOpen);

        if (toggleLabel) {
            toggleLabel.textContent = isOpen ? 'Zatvori meni' : 'Otvori meni';
        }

        iconOpen?.classList.toggle('hidden', isOpen);
        iconClose?.classList.toggle('hidden', ! isOpen);

        if (isOpen) {
            closeButtons[0]?.focus();
        } else {
            toggle.focus();
        }
    };

    toggle.addEventListener('click', () => {
        setOpen(root.dataset.open !== 'true');
    });

    backdrop?.addEventListener('click', () => setOpen(false));

    closeButtons.forEach((button) => {
        button.addEventListener('click', () => setOpen(false));
    });

    panel.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => setOpen(false));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && root.dataset.open === 'true') {
            setOpen(false);
        }
    });

    window.matchMedia('(min-width: 1024px)').addEventListener('change', (event) => {
        if (event.matches) {
            setOpen(false);
        }
    });
}
