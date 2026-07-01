import { onDocumentReady } from './site/on-ready.js';

onDocumentReady(() => {
    initCheckoutForm();
});

function initCheckoutForm() {
    const form = document.querySelector('[data-checkout-form]');

    if (! form) {
        return;
    }

    const deliveryFields = form.querySelector('[data-checkout-delivery-fields]');
    const deliveryMinimumNotice = form.querySelector('[data-checkout-delivery-minimum]');
    const deliveryMinimumHint = form.querySelector('[data-checkout-delivery-minimum-hint]');
    const deliveryFeeNotice = form.querySelector('[data-checkout-delivery-fee-notice]');
    const submitButton = form.querySelector('[data-checkout-submit]');
    const addressInput = form.querySelector('#address');
    const summaryDeliveryRow = document.querySelector('[data-checkout-summary-delivery-row]');
    const summaryDelivery = document.querySelector('[data-checkout-summary-delivery]');
    const summaryTotal = document.querySelector('[data-checkout-summary-total]');
    const options = [...form.querySelectorAll('[data-fulfillment-option]')];
    const deliveryMinimum = Number(form.dataset.deliveryMinimum ?? 0);
    const deliveryFreeAbove = Number(form.dataset.deliveryFreeAbove ?? 0);
    const deliveryAreaKey = form.dataset.deliveryAreaKey ?? '';
    const cartSubtotal = Number(form.dataset.cartSubtotal ?? 0);
    const deliveryAreaFees = parseDeliveryAreaFees(form.dataset.deliveryAreaFees);

    const formatAmount = (amount) => new Intl.NumberFormat('sr-RS', {
        maximumFractionDigits: 0,
    }).format(amount);

    const calculateDeliveryFee = () => {
        if (! deliveryAreaKey || ! Object.hasOwn(deliveryAreaFees, deliveryAreaKey)) {
            return null;
        }

        if (cartSubtotal >= deliveryFreeAbove) {
            return 0;
        }

        return deliveryAreaFees[deliveryAreaKey];
    };

    const syncFulfillment = () => {
        const selected = options.find((option) => option.checked);
        const isDelivery = selected?.value === 'delivery';
        const deliveryMinimumMet = cartSubtotal >= deliveryMinimum;
        const deliveryFee = isDelivery ? calculateDeliveryFee() : null;
        const hasConfiguredArea = deliveryFee !== null;
        const orderTotal = cartSubtotal + (hasConfiguredArea ? deliveryFee : 0);

        if (deliveryFields) {
            deliveryFields.hidden = ! isDelivery;
        }

        if (addressInput) {
            addressInput.required = isDelivery;
        }

        if (deliveryMinimumNotice) {
            deliveryMinimumNotice.classList.toggle('site-checkout-delivery-notice--warning', isDelivery && ! deliveryMinimumMet);
        }

        if (deliveryMinimumHint) {
            if (isDelivery && ! deliveryMinimumMet) {
                const remaining = Math.max(deliveryMinimum - cartSubtotal, 0);
                deliveryMinimumHint.textContent = `Još ${formatAmount(remaining)} RSD do minimuma.`;
                deliveryMinimumHint.hidden = false;
            } else {
                deliveryMinimumHint.hidden = true;
            }
        }

        if (deliveryFeeNotice) {
            if (isDelivery && hasConfiguredArea) {
                deliveryFeeNotice.textContent = deliveryFee > 0
                    ? `Cena dostave: ${formatAmount(deliveryFee)} RSD`
                    : 'Dostava je besplatna za ovu porudžbinu.';
                deliveryFeeNotice.hidden = false;
            } else {
                deliveryFeeNotice.hidden = true;
            }
        }

        if (summaryDeliveryRow) {
            summaryDeliveryRow.hidden = ! (isDelivery && hasConfiguredArea);
        }

        if (summaryDelivery && isDelivery && hasConfiguredArea) {
            summaryDelivery.textContent = deliveryFee > 0
                ? `${formatAmount(deliveryFee)} RSD`
                : 'Besplatno';
        }

        if (summaryTotal) {
            summaryTotal.textContent = `${formatAmount(isDelivery && hasConfiguredArea ? orderTotal : cartSubtotal)} RSD`;
        }

        if (submitButton) {
            submitButton.disabled = isDelivery && ! deliveryMinimumMet;
        }
    };

    options.forEach((option) => {
        option.addEventListener('change', syncFulfillment);
    });

    syncFulfillment();
}

function parseDeliveryAreaFees(rawValue) {
    if (! rawValue) {
        return {};
    }

    try {
        const parsed = JSON.parse(rawValue);

        return typeof parsed === 'object' && parsed !== null ? parsed : {};
    } catch {
        return {};
    }
}
