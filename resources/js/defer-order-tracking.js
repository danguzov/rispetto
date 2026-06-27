import { initEcho } from './echo';

const activeStatuses = new Set([
    'accepted',
    'preparing',
    'ready',
    'out_for_delivery',
]);

function renderTimeline(root, data) {
    const statusContainer = root.querySelector('[data-order-tracking-status]');

    if (! statusContainer) {
        return;
    }

    if (data.cancelled) {
        statusContainer.innerHTML = `
            <div class="site-panel border-red-500/40 p-6 text-center">
                <p class="text-lg font-medium text-rispetto-text">Porudžbina je otkazana</p>
                <p class="mt-2 text-sm text-rispetto-muted">Kontaktirajte restoran ako imate pitanja.</p>
            </div>
        `;

        return;
    }

    const items = data.timeline.map((step, index) => {
        const isComplete = data.current_index !== -1 && index < data.current_index;
        const isCurrent = data.current_index === index;
        const classes = [
            'site-order-timeline__item',
            isComplete ? 'site-order-timeline__item--complete' : '',
            isCurrent ? 'site-order-timeline__item--current' : '',
        ].filter(Boolean).join(' ');

        const eta = isCurrent
            && data.estimated_ready_at_time
            && activeStatuses.has(data.status)
            ? `<p class="mt-2 text-sm font-medium text-rispetto-accent" data-order-eta>Spremno oko ${data.estimated_ready_at_time}</p>`
            : '';

        return `
            <li class="${classes}" data-timeline-index="${index}">
                <div class="site-order-timeline__marker" aria-hidden="true"></div>
                <div class="min-w-0">
                    <p class="text-base font-medium text-rispetto-text">${step.label}</p>
                    <p class="mt-1 text-sm text-rispetto-muted">${step.customer}</p>
                    ${eta}
                </div>
            </li>
        `;
    }).join('');

    statusContainer.innerHTML = `<ol class="site-order-timeline" data-order-timeline>${items}</ol>`;
}

async function refreshTracking(root) {
    const statusUrl = root.dataset.statusUrl;

    if (! statusUrl) {
        return;
    }

    const response = await fetch(statusUrl, {
        headers: {
            Accept: 'application/json',
        },
    });

    if (! response.ok) {
        return;
    }

    const data = await response.json();
    renderTimeline(root, data);
}

function bootOrderTracking() {
    const root = document.querySelector('[data-order-tracking]');

    if (! root) {
        return;
    }

    const token = root.dataset.trackingToken;
    const echo = initEcho();

    if (! echo || ! token) {
        return;
    }

    echo.channel(`order.${token}`)
        .listen('.OrderStatusUpdated', (payload) => {
            if (payload && typeof payload === 'object' && Array.isArray(payload.timeline)) {
                renderTimeline(root, payload);
                return;
            }

            refreshTracking(root);
        });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootOrderTracking);
} else {
    bootOrderTracking();
}
