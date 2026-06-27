function supportsPushNotifications() {
    return 'serviceWorker' in navigator
        && 'PushManager' in window
        && 'Notification' in window;
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let index = 0; index < rawData.length; index += 1) {
        outputArray[index] = rawData.charCodeAt(index);
    }

    return outputArray;
}

async function registerServiceWorker() {
    return navigator.serviceWorker.register('/sw.js');
}

async function subscribeToPush(root) {
    const publicKey = root.dataset.vapidPublicKey;
    const subscribeUrl = root.dataset.pushSubscribeUrl;

    if (! publicKey || ! subscribeUrl) {
        return;
    }

    const permission = await Notification.requestPermission();

    if (permission !== 'granted') {
        throw new Error('Dozvola za obaveštenja nije data.');
    }

    const registration = await registerServiceWorker();
    let subscription = await registration.pushManager.getSubscription();

    if (! subscription) {
        subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(publicKey),
        });
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const response = await fetch(subscribeUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
        },
        body: JSON.stringify(subscription.toJSON()),
    });

    if (! response.ok) {
        throw new Error('Neuspešna prijava na obaveštenja.');
    }
}

function setButtonState(button, state) {
    button.dataset.pushState = state;
    button.disabled = state === 'loading' || state === 'subscribed';

    if (state === 'loading') {
        button.textContent = 'Prijavljivanje...';
        return;
    }

    if (state === 'subscribed') {
        button.textContent = 'Obaveštenja uključena';
        return;
    }

    if (state === 'unsupported') {
        button.textContent = 'Obaveštenja nisu dostupna';
        return;
    }

    button.textContent = 'Obavesti me';
}

function bootOrderPush() {
    const root = document.querySelector('[data-order-tracking]');
    const button = document.querySelector('[data-order-push-subscribe]');

    if (! root || ! button) {
        return;
    }

    if (! supportsPushNotifications() || ! root.dataset.vapidPublicKey) {
        setButtonState(button, 'unsupported');
        return;
    }

    if (Notification.permission === 'granted') {
        setButtonState(button, 'subscribed');
    }

    button.addEventListener('click', async () => {
        setButtonState(button, 'loading');

        try {
            await subscribeToPush(root);
            setButtonState(button, 'subscribed');
        } catch (error) {
            console.warn(error);
            setButtonState(button, 'idle');
            button.textContent = 'Pokušaj ponovo';
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootOrderPush);
} else {
    bootOrderPush();
}
