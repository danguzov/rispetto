import { onDocumentReady } from './site/on-ready.js';

onDocumentReady(() => {
    initCheckoutForm();
});

function initCheckoutForm() {
    const form = document.querySelector('[data-checkout-form]');

    if (! form) {
        return;
    }

    const addressBlock = form.querySelector('[data-checkout-address]');
    const addressInput = form.querySelector('#address');
    const options = [...form.querySelectorAll('[data-fulfillment-option]')];

    const syncFulfillment = () => {
        const selected = options.find((option) => option.checked);
        const isDelivery = selected?.value === 'delivery';

        if (addressBlock) {
            addressBlock.hidden = ! isDelivery;
        }

        if (addressInput) {
            addressInput.required = isDelivery;
        }
    };

    options.forEach((option) => {
        option.addEventListener('change', syncFulfillment);
    });

    syncFulfillment();
}
