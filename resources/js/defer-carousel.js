import { onDocumentReady } from './site/on-ready.js';

onDocumentReady(() => {
    initTestimonialsCarousel();
});

function initTestimonialsCarousel() {
    const root = document.querySelector('[data-testimonials]');

    if (! root) {
        return;
    }

    const track = root.querySelector('[data-testimonials-track]');
    const slides = [...root.querySelectorAll('[data-testimonials-slide]')];

    if (! track || slides.length === 0) {
        return;
    }

    const getActiveDots = () => {
        const isDesktop = window.matchMedia('(min-width: 768px)').matches;
        const dotsGroup = isDesktop ? 'desktop' : 'mobile';

        return [...root.querySelectorAll(`[data-testimonials-dots="${dotsGroup}"] [data-testimonials-dot]`)];
    };

    let activeIndex = 0;
    let isDragging = false;
    let startX = 0;
    let currentTranslate = 0;
    let previousTranslate = 0;

    const visibleCount = () => (window.matchMedia('(min-width: 768px)').matches ? 3 : 1);

    const maxIndex = () => Math.max(0, slides.length - visibleCount());

    const getStep = () => {
        const gap = Number.parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap) || 24;
        const slideWidth = slides[0].getBoundingClientRect().width;

        return slideWidth + gap;
    };

    const getTranslateForIndex = (index) => -index * getStep();

    const clampTranslate = (translate) => {
        const minTranslate = getTranslateForIndex(maxIndex());
        const maxTranslate = 0;

        return Math.min(maxTranslate, Math.max(minTranslate, translate));
    };

    const setTransform = (translate, animate = true) => {
        track.style.transition = animate ? 'transform 300ms ease-out' : 'none';
        track.style.transform = `translateX(${translate}px)`;
    };

    const updateDots = (index) => {
        getActiveDots().forEach((dot) => {
            const dotIndex = Number(dot.dataset.testimonialsDot ?? 0);
            const isActive = dotIndex === index;
            dot.dataset.active = isActive ? 'true' : 'false';
            dot.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });
    };

    const update = (animate = true) => {
        const index = Math.min(activeIndex, maxIndex());
        activeIndex = index;

        const translate = getTranslateForIndex(index);
        currentTranslate = translate;
        previousTranslate = translate;

        setTransform(translate, animate);
        updateDots(index);
    };

    const finishDrag = () => {
        if (! isDragging) {
            return;
        }

        isDragging = false;
        track.classList.remove('is-dragging');

        const movedBy = currentTranslate - previousTranslate;
        const threshold = getStep() * 0.15;

        if (movedBy < -threshold) {
            activeIndex = Math.min(activeIndex + 1, maxIndex());
        } else if (movedBy > threshold) {
            activeIndex = Math.max(activeIndex - 1, 0);
        }

        update(true);
    };

    const onPointerDown = (event) => {
        if (event.pointerType === 'mouse' && event.button !== 0) {
            return;
        }

        isDragging = true;
        startX = event.clientX;
        track.classList.add('is-dragging');
        track.setPointerCapture(event.pointerId);
        track.style.transition = 'none';
    };

    const onPointerMove = (event) => {
        if (! isDragging) {
            return;
        }

        const delta = event.clientX - startX;
        currentTranslate = clampTranslate(previousTranslate + delta);
        setTransform(currentTranslate, false);
    };

    const onPointerUp = (event) => {
        if (track.hasPointerCapture(event.pointerId)) {
            track.releasePointerCapture(event.pointerId);
        }

        finishDrag();
    };

    root.querySelectorAll('[data-testimonials-dot]').forEach((dot) => {
        dot.addEventListener('click', () => {
            activeIndex = Number(dot.dataset.testimonialsDot ?? 0);
            update();
        });
    });

    track.addEventListener('pointerdown', onPointerDown);
    track.addEventListener('pointermove', onPointerMove);
    track.addEventListener('pointerup', onPointerUp);
    track.addEventListener('pointercancel', onPointerUp);
    track.addEventListener('lostpointercapture', finishDrag);
    track.addEventListener('dragstart', (event) => event.preventDefault());

    window.addEventListener('resize', () => update(false));

    update(false);
}
