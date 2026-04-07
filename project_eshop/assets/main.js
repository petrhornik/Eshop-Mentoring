import netteForms from 'nette-forms';

netteForms.initOnLoad();

const CART_STORAGE_KEY = 'eshop-cart';

const escapeHtml = (value) => String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;');

const formatPrice = (value) => {
    const amount = Number(value);

    if (!Number.isFinite(amount) || amount <= 0) {
        return 'Cena na dotaz';
    }

    return `${amount.toLocaleString('cs-CZ')} Kč`;
};

const getCart = () => {
    try {
        const rawValue = localStorage.getItem(CART_STORAGE_KEY);
        const parsedValue = rawValue ? JSON.parse(rawValue) : [];
        return Array.isArray(parsedValue) ? parsedValue : [];
    } catch {
        return [];
    }
};

const saveCart = (cart) => {
    localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart));
    document.dispatchEvent(new CustomEvent('cart:updated', { detail: cart }));
};

const cartSummary = (cart) => {
    const items = cart.length;
    const quantity = cart.reduce((total, item) => total + (item.quantity ?? 0), 0);
    const total = cart.reduce((sum, item) => {
        const price = Number(item.price);
        return Number.isFinite(price) ? sum + (price * item.quantity) : sum;
    }, 0);

    return { items, quantity, total };
};

const removeFromCart = (id) => {
    const nextCart = getCart().filter((item) => String(item.id) !== String(id));
    saveCart(nextCart);
};

const clearCart = () => saveCart([]);

const addToCart = (product) => {
    const cart = getCart();
    const existingProduct = cart.find((item) => String(item.id) === String(product.id));

    if (existingProduct) {
        existingProduct.quantity += 1;
    } else {
        cart.push({
            ...product,
            quantity: 1,
        });
    }

    saveCart(cart);
};

const createCartItemMarkup = (item, compact = false) => {
    const wrapperClass = compact ? 'cart-preview-item' : 'cart-page-item';
    const imageClass = compact ? 'cart-preview-image' : 'cart-page-image';
    const copyClass = compact ? 'cart-preview-copy' : 'cart-page-copy';
    const metaClass = compact ? 'cart-preview-meta' : 'cart-page-meta';
    const priceClass = compact ? 'cart-preview-price' : 'cart-page-price';
    const removeClass = compact ? 'cart-preview-remove' : 'cart-page-remove';
    const safeName = escapeHtml(item.name);
    const safeSellerName = escapeHtml(item.sellerName || 'Neznámý prodejce');
    const safeImage = escapeHtml(item.image);
    const safeUrl = escapeHtml(item.url);
    const safeId = escapeHtml(item.id);

    return `
        <article class="${wrapperClass}">
            <img src="${safeImage}" alt="${safeName}" class="${imageClass}">
            <div class="${copyClass}">
                <a href="${safeUrl}">${safeName}</a>
                <div class="${metaClass}">
                    <div>${safeSellerName}</div>
                    <div>${item.quantity} ks</div>
                </div>
            </div>
            <div class="cart-page-actions">
                <strong class="${priceClass}">${formatPrice(item.price)}</strong>
                ${compact ? '' : `<span class="cart-qty-pill">${item.quantity} ks</span>`}
                <button type="button" class="${removeClass}" data-cart-remove="${safeId}">Smazat</button>
            </div>
        </article>
    `;
};

const renderNavCart = (cart) => {
    const countBadge = document.querySelector('[data-cart-count]');
    const countLabel = document.querySelector('[data-cart-count-label]');
    const previewList = document.querySelector('[data-cart-preview-list]');
    const previewEmpty = document.querySelector('[data-cart-preview-empty]');

    if (!countBadge || !countLabel || !previewList || !previewEmpty) {
        return;
    }

    const summary = cartSummary(cart);
    countBadge.textContent = String(summary.quantity);
    countLabel.textContent = `${summary.quantity} ks`;

    if (cart.length === 0) {
        previewList.innerHTML = '';
        previewEmpty.hidden = false;
        return;
    }

    previewEmpty.hidden = true;
    previewList.innerHTML = cart.slice(0, 4).map((item) => createCartItemMarkup(item, true)).join('');
};

const renderCartPage = (cart) => {
    const pageList = document.querySelector('[data-cart-page-list]');
    const pageEmpty = document.querySelector('[data-cart-page-empty]');
    const pageCount = document.querySelector('[data-cart-page-count]');
    const summaryQty = document.querySelector('[data-cart-summary-qty]');
    const summaryItems = document.querySelector('[data-cart-summary-items]');
    const summaryTotal = document.querySelector('[data-cart-summary-total]');

    if (!pageList || !pageEmpty || !pageCount || !summaryQty || !summaryItems || !summaryTotal) {
        return;
    }

    const summary = cartSummary(cart);
    pageCount.textContent = `${summary.quantity} ks`;
    summaryQty.textContent = String(summary.quantity);
    summaryItems.textContent = String(summary.items);
    summaryTotal.textContent = formatPrice(summary.total);

    if (cart.length === 0) {
        pageList.innerHTML = '';
        pageEmpty.hidden = false;
        return;
    }

    pageEmpty.hidden = true;
    pageList.innerHTML = `<div class="cart-page-list">${cart.map((item) => createCartItemMarkup(item)).join('')}</div>`;
};

const renderCart = () => {
    const cart = getCart();
    renderNavCart(cart);
    renderCartPage(cart);
};

const initCartFlyout = () => {
    const hoverArea = document.querySelector('[data-cart-hover-area]');
    const flyout = document.querySelector('[data-cart-flyout]');

    if (!hoverArea || !flyout) {
        return;
    }

    let hideTimeout = null;

    const openFlyout = () => {
        if (hideTimeout !== null) {
            window.clearTimeout(hideTimeout);
            hideTimeout = null;
        }

        flyout.hidden = false;
    };

    const closeFlyout = () => {
        hideTimeout = window.setTimeout(() => {
            flyout.hidden = true;
        }, 120);
    };

    hoverArea.addEventListener('mouseenter', openFlyout);
    hoverArea.addEventListener('mouseleave', closeFlyout);
    flyout.addEventListener('mouseenter', openFlyout);
    flyout.addEventListener('mouseleave', closeFlyout);
};

const initCartAddButtons = () => {
    document.addEventListener('click', (event) => {
        const addButton = event.target.closest('[data-cart-add]');

        if (addButton) {
            addToCart({
                id: addButton.dataset.productId,
                name: addButton.dataset.productName,
                price: addButton.dataset.productPrice,
                image: addButton.dataset.productImage,
                sellerName: addButton.dataset.productSeller,
                url: addButton.dataset.productUrl,
            });

            renderCart();
            return;
        }

        const removeButton = event.target.closest('[data-cart-remove]');

        if (removeButton) {
            removeFromCart(removeButton.dataset.cartRemove);
            renderCart();
            return;
        }

        const clearButton = event.target.closest('[data-cart-clear]');

        if (clearButton) {
            clearCart();
            renderCart();
        }
    });
};

document.addEventListener('DOMContentLoaded', () => {
    initCartFlyout();
    initCartAddButtons();
    renderCart();
});

document.addEventListener('cart:updated', () => {
    renderCart();
});
