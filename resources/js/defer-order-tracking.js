import { initEcho } from './echo';

const activeStatuses = new Set([
    'accepted',
    'preparing',
    'ready',
    'out_for_delivery',
]);

let countdownInterval = null;

function formatCountdownLabel(isoString) {
    if (! isoString) {
        return null;
    }

    const target = new Date(isoString).getTime();

    if (Number.isNaN(target)) {
        return null;
    }

    const diffMs = target - Date.now();

    if (diffMs <= 0) {
        return 'Spremno uskoro';
    }

    const totalSeconds = Math.floor(diffMs / 1000);
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;

    if (minutes >= 1) {
        return `Spremno za ~${minutes} min`;
    }

    return `Spremno za ${seconds} sek`;
}

function updateCountdownElements(root) {
    root.querySelectorAll('[data-order-countdown]').forEach((element) => {
        const readyAt = element.dataset.readyAt;

        if (! readyAt) {
            return;
        }

        const label = formatCountdownLabel(readyAt);

        if (label) {
            element.textContent = label;
        }
    });
}

function startCountdown(root) {
    if (countdownInterval) {
        window.clearInterval(countdownInterval);
    }

    updateCountdownElements(root);

    if (! root.querySelector('[data-order-countdown]')) {
        return;
    }

    countdownInterval = window.setInterval(() => updateCountdownElements(root), 1000);
}

function countdownMarkup(data) {
    if (
        ! activeStatuses.has(data.status)
        || ! data.estimated_ready_at
    ) {
        return '';
    }

    return `<p class="mt-3 text-lg font-semibold text-rispetto-accent" data-order-countdown data-ready-at="${data.estimated_ready_at}">${formatCountdownLabel(data.estimated_ready_at) ?? ''}</p>`;
}

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

        const eta = isCurrent ? countdownMarkup(data) : '';

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
    startCountdown(root);
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

function setupCopyLink(root) {
    const copyButton = root.querySelector('[data-order-copy-link]');
    const linkInput = root.querySelector('[data-order-tracking-link]');
    const feedback = root.querySelector('[data-order-copy-feedback]');

    if (! copyButton || ! linkInput) {
        return;
    }

    copyButton.addEventListener('click', async () => {
        const url = linkInput.value;

        try {
            if (navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(url);
            } else {
                linkInput.select();
                document.execCommand('copy');
            }

            if (feedback) {
                feedback.classList.remove('hidden');
                window.setTimeout(() => feedback.classList.add('hidden'), 2500);
            }
        } catch {
            linkInput.select();
        }
    });
}

function bootOrderTracking() {
    const root = document.querySelector('[data-order-tracking]');

    if (! root) {
        return;
    }

    setupCopyLink(root);
    startCountdown(root);

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
