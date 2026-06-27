import { onDocumentReady } from './site/on-ready.js';

onDocumentReady(() => {
    initCart();
});

function initCart() {
    const addUrl = document.body.dataset.cartAddUrl;

    if (! addUrl) {
        return;
    }

    const dialog = document.querySelector('[data-cart-variant-dialog]');
    const toast = document.querySelector('[data-cart-toast]');
    let pendingButton = null;

    refreshCartBadge();

    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-cart-add]');

        if (! button || button.disabled) {
            return;
        }

        event.preventDefault();

        const menuItemId = Number(button.dataset.menuItemId);
        const hasVariants = button.dataset.hasVariants === 'true';
        const pizzaSizeFilter = button.dataset.pizzaSizeFilter === 'true';

        if (! hasVariants) {
            await submitAdd(button, menuItemId, null);
            return;
        }

        if (pizzaSizeFilter) {
            const variantLabel = resolvePizzaVariantLabel(button);

            if (! variantLabel) {
                showToast(toast, 'Izaberite veličinu pice.');
                return;
            }

            await submitAdd(button, menuItemId, variantLabel);
            return;
        }

        pendingButton = button;
        openVariantDialog(dialog, button);
    });

    dialog?.addEventListener('click', async (event) => {
        if (event.target === dialog) {
            closeVariantDialog(dialog);
            return;
        }

        const closeButton = event.target.closest('[data-cart-variant-close]');

        if (closeButton) {
            closeVariantDialog(dialog);
            return;
        }

        const option = event.target.closest('[data-cart-variant-option]');

        if (! option || ! pendingButton) {
            return;
        }

        await submitAdd(
            pendingButton,
            Number(pendingButton.dataset.menuItemId),
            option.dataset.variantLabel ?? null,
        );

        closeVariantDialog(dialog);
        pendingButton = null;
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && dialog?.open) {
            closeVariantDialog(dialog);
            pendingButton = null;
        }
    });

    initCartPage();
}

function initCartPage() {
    const cartPage = document.querySelector('[data-cart-page]');

    if (! cartPage) {
        return;
    }

    cartPage.addEventListener('click', async (event) => {
        const item = event.target.closest('[data-cart-item]');

        if (! item) {
            return;
        }

        const key = item.dataset.cartKey;

        if (event.target.closest('[data-cart-increase]')) {
            event.preventDefault();
            const quantity = Number(item.querySelector('[data-cart-quantity]')?.textContent ?? 1);
            await mutateCartItem(item, key, 'update', quantity + 1);
            return;
        }

        if (event.target.closest('[data-cart-decrease]')) {
            event.preventDefault();
            const quantity = Number(item.querySelector('[data-cart-quantity]')?.textContent ?? 1);

            if (quantity <= 1) {
                await mutateCartItem(item, key, 'remove');
                return;
            }

            await mutateCartItem(item, key, 'update', quantity - 1);
            return;
        }

        if (event.target.closest('[data-cart-remove]')) {
            event.preventDefault();
            await mutateCartItem(item, key, 'remove');
        }
    });
}

async function mutateCartItem(item, key, action, quantity = null) {
    const updateBaseUrl = document.body.dataset.cartUpdateUrl;
    const toast = document.querySelector('[data-cart-toast]');

    if (! updateBaseUrl) {
        return;
    }

    item.dataset.loading = 'true';

    try {
        const url = `${updateBaseUrl}/${encodeURIComponent(key)}`;
        const response = await fetch(url, {
            method: action === 'remove' ? 'DELETE' : 'PATCH',
            headers: cartHeaders(),
            body: action === 'remove' ? undefined : JSON.stringify({ quantity }),
        });

        const payload = await response.json().catch(() => ({}));

        if (! response.ok) {
            showToast(toast, payload.message ?? 'Ažuriranje korpe nije uspelo.');
            return;
        }

        updateCartBadge(payload.item_count ?? 0);

        if (payload.is_empty) {
            window.location.reload();
            return;
        }

        if (action === 'remove') {
            item.remove();
        } else {
            syncCartRow(item, payload, key);
        }

        updateCartFooter(payload);
    } catch {
        showToast(toast, 'Greška u mreži. Pokušajte ponovo.');
    } finally {
        item.dataset.loading = 'false';
    }
}

function syncCartRow(row, payload, changedKey) {
    const changedItem = payload.items?.find((item) => item.key === changedKey);

    if (! changedItem) {
        row.remove();
        return;
    }

    const quantity = row.querySelector('[data-cart-quantity]');
    const lineTotal = row.querySelector('[data-cart-line-total]');

    if (quantity) {
        quantity.textContent = String(changedItem.quantity);
    }

    if (lineTotal) {
        lineTotal.textContent = `${formatPrice(changedItem.unit_price * changedItem.quantity)} RSD`;
    }
}

function updateCartFooter(payload) {
    const subtotal = document.querySelector('[data-cart-subtotal]');
    const itemCount = document.querySelector('[data-cart-item-count]');

    if (subtotal) {
        subtotal.textContent = `${formatPrice(payload.subtotal)} RSD`;
    }

    if (itemCount) {
        itemCount.textContent = String(payload.item_count ?? 0);
    }
}

async function submitAdd(button, menuItemId, variantLabel) {
    const addUrl = document.body.dataset.cartAddUrl;
    const label = button.querySelector('[data-cart-add-label]');
    const originalText = label?.textContent ?? 'Dodaj u korpu';

    setButtonLoading(button, label, true, originalText);

    try {
        const response = await fetch(addUrl, {
            method: 'POST',
            headers: cartHeaders(),
            body: JSON.stringify({
                menu_item_id: menuItemId,
                variant_label: variantLabel,
                quantity: 1,
            }),
        });

        const payload = await response.json().catch(() => ({}));

        if (! response.ok) {
            const message = payload.message
                ?? payload.errors?.variant_label?.[0]
                ?? payload.errors?.ordering?.[0]
                ?? 'Dodavanje u korpu nije uspelo.';

            showToast(document.querySelector('[data-cart-toast]'), message);
            return;
        }

        updateCartBadge(payload.item_count ?? 0);
        showToast(document.querySelector('[data-cart-toast]'), 'Dodato u korpu.');
        flashAdded(button);
    } catch {
        showToast(document.querySelector('[data-cart-toast]'), 'Greška u mreži. Pokušajte ponovo.');
    } finally {
        setButtonLoading(button, label, false, originalText);
    }
}

async function refreshCartBadge() {
    const showUrl = document.body.dataset.cartShowUrl;

    if (! showUrl) {
        return;
    }

    try {
        const response = await fetch(showUrl, {
            headers: { Accept: 'application/json' },
        });

        if (! response.ok) {
            return;
        }

        const payload = await response.json();
        updateCartBadge(payload.item_count ?? 0);
    } catch {
        // Badge refresh is best-effort on page load.
    }
}

function openVariantDialog(dialog, button) {
    if (! dialog) {
        return;
    }

    const title = dialog.querySelector('[data-cart-variant-title]');
    const options = dialog.querySelector('[data-cart-variant-options]');
    const variants = parseVariants(button.dataset.variants);

    if (! options || variants.length === 0) {
        return;
    }

    if (title) {
        title.textContent = button.dataset.itemName ?? 'Jelo';
    }

    options.innerHTML = '';

    variants.forEach((variant) => {
        const option = document.createElement('button');
        option.type = 'button';
        option.className = 'site-cart-variant-option';
        option.dataset.cartVariantOption = 'true';
        option.dataset.variantLabel = variant.label;
        option.innerHTML = `
            <span class="site-cart-variant-option__label">${variant.label}</span>
            <span class="site-cart-variant-option__price">${formatPrice(variant.price)} RSD</span>
        `;
        options.append(option);
    });

    dialog.showModal();
}

function closeVariantDialog(dialog) {
    dialog?.close();
}

function resolvePizzaVariantLabel(button) {
    const panel = button.closest('[data-pizza-size-panel]');

    if (! panel) {
        return null;
    }

    const sizeKey = panel.dataset.activePizzaSize;
    const variants = parseVariants(button.dataset.variants);

    return variants.find((variant) => normalizeSizeKey(variant.label) === sizeKey)?.label ?? null;
}

function parseVariants(raw) {
    if (! raw) {
        return [];
    }

    try {
        const parsed = JSON.parse(raw);

        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
}

function normalizeSizeKey(label) {
    return String(label).replace(/\D+/g, '');
}

function updateCartBadge(count) {
    const badge = document.querySelector('[data-cart-badge]');

    if (! badge) {
        return;
    }

    const safeCount = Number(count) || 0;

    if (safeCount <= 0) {
        badge.hidden = true;
        badge.textContent = '0';
        return;
    }

    badge.hidden = false;
    badge.textContent = safeCount > 99 ? '99+' : String(safeCount);
}

function showToast(toast, message) {
    if (! toast) {
        return;
    }

    toast.textContent = message;
    toast.hidden = false;
    toast.dataset.visible = 'true';

    window.clearTimeout(showToast.timeoutId);
    showToast.timeoutId = window.setTimeout(() => {
        toast.hidden = true;
        toast.dataset.visible = 'false';
    }, 3200);
}

function flashAdded(button) {
    button.classList.add('site-cart-add--added');
    window.setTimeout(() => button.classList.remove('site-cart-add--added'), 700);
}

function setButtonLoading(button, label, isLoading, originalText) {
    button.disabled = isLoading || button.getAttribute('aria-disabled') === 'true';
    button.dataset.loading = isLoading ? 'true' : 'false';

    if (label) {
        label.textContent = isLoading ? 'Dodajem...' : originalText;
    }
}

function cartHeaders() {
    return {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrfToken(),
        'X-Requested-With': 'XMLHttpRequest',
    };
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function formatPrice(price) {
    return new Intl.NumberFormat('sr-RS', {
        maximumFractionDigits: 0,
    }).format(Number(price) || 0);
}
