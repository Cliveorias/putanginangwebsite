document.addEventListener('DOMContentLoaded', () => {
    
    // --- NAVIGATION LOGIC ---
    const hamburgerBtn = document.getElementById('hamburger-btn');
    const closeBtn = document.getElementById('close-btn');
    const sideNav = document.getElementById('side-nav');
    const overlay = document.getElementById('overlay');

    const toggleMenu = (isOpen) => {
        sideNav?.classList.toggle('active', isOpen);
        overlay?.classList.toggle('active', isOpen);
        document.body.style.overflow = isOpen ? 'hidden' : 'auto';
    };

    hamburgerBtn?.addEventListener('click', () => toggleMenu(true));
    closeBtn?.addEventListener('click', () => toggleMenu(false));
    overlay?.addEventListener('click', () => toggleMenu(false));

    // --- CART KEY LOGIC (Kaniya-kaniyang Cart) ---
    const currentCartKey = (typeof cartKey !== 'undefined') ? cartKey : 'cart_guest';

    const cartItemsList = document.getElementById('cart-items-list');
    const subtotalDisplay = document.getElementById('subtotal-display');
    const vatDisplay = document.getElementById('vat-display'); 
    const discountDisplay = document.getElementById('discount-display'); 
    const totalDisplay = document.getElementById('total-display');

    // --- RENDER CART (SMOOTH UPDATE) ---
    const renderCart = () => {
        let cart = JSON.parse(localStorage.getItem(currentCartKey)) || [];
        if (!cartItemsList) return;
        
        cartItemsList.innerHTML = '';
        let subtotal = 0;
        let highestPrice = 0; 

        if (cart.length === 0) {
            cartItemsList.innerHTML = `
                <div class="empty-msg">
                    <i class="fa-solid fa-basket-shopping"></i>
                    <p>Your cart is empty. <br> Add items from the Popular section below!</p>
                </div>`;
            updateTotals(0, 0);
            return;
        }

        cart.forEach((item) => {
            // FIX: Siguraduhin na tunay na Numero ang ginagamit sa Math
            const price = Number(item.price) || 0;
            const qty = Number(item.qty) || 0;
            const itemTotal = price * qty;
            subtotal += itemTotal;
            
            if (price > highestPrice) {
                highestPrice = price;
            }

            const displayName = item.name || item.item_name;
            const safeName = displayName.replace(/'/g, "\\'"); 

            cartItemsList.innerHTML += `
                <div class="cart-item">
                    <div class="cart-item-info">
                        <h4>${displayName}</h4>
                        <span class="u-price">@ PHP ${price.toFixed(2)}</span>
                    </div>
                    <div class="cart-controls">
                        <div>
                            <button class="btn-qty" onclick="changeQty('${safeName}', -1)"><i class="fa-solid fa-minus"></i></button>
                            <span class="qty-display">${qty}</span>
                            <button class="btn-qty" onclick="changeQty('${safeName}', 1)"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="item-total-price">
                        PHP ${itemTotal.toFixed(2)}
                    </div>
                </div>`;
        });
        updateTotals(subtotal, highestPrice);
    };

    const updateTotals = (subtotal, highestPrice) => {
        const discountType = localStorage.getItem('userDiscountType') || 'None';
        let vatAmount = 0, totalDiscount = 0, finalTotal = 0;
        const standardVat = subtotal * 0.12;

        if (discountType === 'Senior' || discountType === 'PWD') {
            const vatExemption = highestPrice * 0.12;
            const twentyPercentOff = highestPrice * 0.20;
            vatAmount = standardVat - vatExemption;
            totalDiscount = vatExemption + twentyPercentOff;
            finalTotal = (subtotal + standardVat) - totalDiscount;
            if(document.getElementById('discount-row')) document.getElementById('discount-row').style.display = 'flex';
        } else {
            vatAmount = standardVat;
            totalDiscount = 0;
            finalTotal = subtotal + vatAmount;
            if(document.getElementById('discount-row')) document.getElementById('discount-row').style.display = 'none';
        }

        // FIX: Siguraduhin na Numbers ang ipapakita sa display
        if(subtotalDisplay) subtotalDisplay.innerText = subtotal.toFixed(2);
        if(vatDisplay) vatDisplay.innerText = vatAmount.toFixed(2);
        if(discountDisplay) discountDisplay.innerText = totalDiscount.toFixed(2);
        if(totalDisplay) totalDisplay.innerText = finalTotal.toFixed(2);
        
        updateBadge();
    };

    // --- FIXED changeQty (Database + LocalStorage Sync) ---
    window.changeQty = (name, amount) => {
        let cart = JSON.parse(localStorage.getItem(currentCartKey)) || [];
        const itemIndex = cart.findIndex(item => (item.name === name || item.item_name === name));
        
        if (itemIndex > -1) {
            cart[itemIndex].qty = Number(cart[itemIndex].qty) + amount;
            
            // DATABASE SYNC: Gamit ang manage_cart.php
            if (currentCartKey !== 'cart_guest') {
                const formData = new FormData();
                formData.append('action', 'update_qty');
                formData.append('name', name);
                formData.append('amount', amount);
                fetch('manage_cart.php', { method: 'POST', body: formData });
            }

            if (cart[itemIndex].qty <= 0) {
                cart.splice(itemIndex, 1);
                showToast(`Removed ${name} from cart.`);
            }
        }
        
        localStorage.setItem(currentCartKey, JSON.stringify(cart));
        localStorage.setItem('myCart', JSON.stringify(cart)); // Bridge key para sa Checkout page
        renderCart(); 
    };

    const updateBadge = () => {
        const cart = JSON.parse(localStorage.getItem(currentCartKey)) || [];
        const totalItems = cart.reduce((sum, item) => sum + Number(item.qty), 0);
        const badge = document.getElementById('cart-badge');
        if (badge) badge.innerText = totalItems;
    };

    const showToast = (msg) => {
        const toast = document.getElementById('toast');
        if (toast) {
            toast.innerText = msg;
            toast.style.visibility = 'visible';
            setTimeout(() => { toast.style.visibility = 'hidden'; }, 2000);
        }
    };

    renderCart();
});

// --- CHECKOUT LOGIC (GUEST WALL) ---
const proceedBtn = document.getElementById('btn-proceed-checkout');
proceedBtn?.addEventListener('click', () => {
    const currentCartKey = (typeof cartKey !== 'undefined') ? cartKey : 'cart_guest';
    if (currentCartKey === 'cart_guest') {
        alert("Ops! Kailangan mo munang mag-login, Kabayan.");
        window.location.href = 'login.html';
        return;
    }

    let cart = JSON.parse(localStorage.getItem(currentCartKey)) || [];
    if (cart.length === 0) {
        alert("Naku! Mag-add muna ng ulam.");
    } else {
        localStorage.setItem('myCart', JSON.stringify(cart));
        window.location.href = 'Checkout.php';
    }
}); 

// --- FIXED addToCart para sa Popular Section ---
function addToCart(name, price, img) {
    const currentCartKey = (typeof cartKey !== 'undefined') ? cartKey : 'cart_guest';
    let cart = JSON.parse(localStorage.getItem(currentCartKey)) || [];
    const existing = cart.find(i => (i.name === name || i.item_name === name));
    
    if (existing) { 
        existing.qty = Number(existing.qty) + 1; 
    } else { 
        cart.push({ name: name, price: price, img: img, qty: 1 }); 
    }
    
    localStorage.setItem(currentCartKey, JSON.stringify(cart));
    localStorage.setItem('myCart', JSON.stringify(cart));

    if (currentCartKey !== 'cart_guest') {
        const formData = new FormData();
        formData.append('action', 'add'); //
        formData.append('name', name);
        formData.append('price', price);
        formData.append('img', img);
        fetch('manage_cart.php', { method: 'POST', body: formData })
        .then(() => location.reload());
    } else {
        location.reload(); 
    }
}