import { initEcho } from './echo';

function applyOrderingAvailability(acceptingOrders, message) {
    const banner = document.querySelector('[data-ordering-banner]');
    const isAccepting = Boolean(acceptingOrders);

    if (banner) {
        banner.dataset.visible = isAccepting ? 'false' : 'true';
        banner.classList.toggle('hidden', isAccepting);

        const messageElement = banner.querySelector('[data-ordering-banner-message]');

        if (messageElement && message) {
            messageElement.textContent = message;
        }
    }

    document.querySelectorAll('[data-cart-add]').forEach((button) => {
        button.disabled = ! isAccepting;
        button.toggleAttribute('aria-disabled', ! isAccepting);

        if (! isAccepting && message) {
            button.title = message;
        } else {
            button.removeAttribute('title');
        }
    });
}

function bootOrderingAvailability() {
    const accepting = document.body.dataset.orderingAccepting === 'true';
    const message = document.body.dataset.orderingMessage ?? '';
    applyOrderingAvailability(accepting, message);

    const echo = initEcho();

    if (! echo) {
        return;
    }

    echo.channel('site.ordering')
        .listen('.OrderingAvailabilityChanged', (payload) => {
            applyOrderingAvailability(payload.accepting_orders, payload.message);
        });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootOrderingAvailability);
} else {
    bootOrderingAvailability();
}

export { applyOrderingAvailability };
