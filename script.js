document.addEventListener('DOMContentLoaded', function() {

    // ==========================================
    // PART 1: BADGE UPDATE
    // ==========================================
    updateBadgeCount(); 

    // ==========================================
    // PART 2: CATEGORY LOGIC
    // ==========================================
    const categories = [
        { id: 'all', name: 'All', icon: '<i class="bx bxs-grid-alt"></i>', count: '28 items' },
        { id: 'lugaw', name: 'Lugaw', icon: '<i class="bx bx-bowl-hot"></i>', count: '3 items' },
        { id: 'goto', name: 'Goto', icon: '<i class="bx bx-bowl-rice"></i>', count: '2 items' },
        { id: 'silog', name: 'Silog', icon: '<i class="bx bx-restaurant"></i>', count: '7 items' },
        { id: 'sizzling', name: 'Sizzling', icon: '<i class="bx bxs-hot"></i>', count: '4 items' },
        { id: 'chow', name: 'Chowfan', icon: '<i class="bx bx-food-tag"></i>', count: '5 items' },
        { id: 'carte', name: 'A la Carte', icon: '<i class="bx bx-dish"></i>', count: '3 items' },
        { id: 'drinks', name: 'Drinks', icon: '<i class="bx bx-drink"></i>', count: '3 items' },
        { id: 'addons', name: 'Add Ons', icon: '<i class="bx bx-plus-medical"></i>', count: '7 items' },
    ];

    const categoryContainer = document.getElementById('categoryContainer');
    const menuCards = document.querySelectorAll('.menu-card'); 
    let activeCategory = 'all'; 

    function renderCategories() {
        if (!categoryContainer) return; 
        categoryContainer.innerHTML = '';
        categories.forEach(cat => {
            const btn = document.createElement('div');
            const isActive = activeCategory === cat.id ? 'active' : '';
            btn.className = `category-card ${isActive}`;
            btn.innerHTML = `
                <div class="cat-icon">${cat.icon}</div>
                <span class="cat-name">${cat.name}</span>
                <span class="cat-count">${cat.count}</span>
            `;
            btn.addEventListener('click', () => {
                document.querySelectorAll('.category-card').forEach(c => c.classList.remove('active'));
                btn.classList.add('active');
                activeCategory = cat.id;
                filterMenuCards(cat.id);
            });
            categoryContainer.appendChild(btn);
        });
    }

    function filterMenuCards(category) {
        menuCards.forEach(card => {
            const cardCategory = card.getAttribute('data-category'); 
            if (category === 'all' || cardCategory === category) {
                card.style.display = 'flex'; 
            } else {
                card.style.display = 'none';
            }
        });
        
        const activeContainer = menuCards[0]?.parentNode;
        if(activeContainer) reorderMenuCards(activeContainer);
    }
    renderCategories();


    // ==========================================
    // PART 3: CART PAGE RENDER TRIGGER
    // ==========================================
    const cartListElement = document.getElementById('cart-items-list');
    if (cartListElement) {
        renderCartPage();
    }


    // ==========================================
    // PART 5: SEARCH FUNCTIONALITY
    // ==========================================
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchText = e.target.value.toLowerCase(); 
            menuCards.forEach(card => {
                const titleElement = card.querySelector('h3, h4, .food-name'); 
                if (titleElement) {
                    const foodName = titleElement.innerText.toLowerCase();
                    if (foodName.includes(searchText)) card.style.display = 'flex'; 
                    else card.style.display = 'none';
                }
            });
            if(searchText.length > 0) {
                 document.querySelectorAll('.category-card').forEach(c => c.classList.remove('active'));
            }
        });
    }

    // ==========================================
    // PART 6: HEART / LIKE FUNCTIONALITY (UPDATED WITH SORTING)
    // ==========================================
    const allMenuCards = document.querySelectorAll('.menu-card, .menu-card-scroll');
    allMenuCards.forEach((card, index) => {
        if (!card.hasAttribute('data-original-index')) {
            card.setAttribute('data-original-index', index);
        }
    });

    loadFavoritesState();

    const favBtns = document.querySelectorAll('.btn-fav, .btn-heart');
    
    favBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault(); 

            const currentCard = this.closest('.menu-card') || this.closest('.menu-card-scroll');
            if (!currentCard) return;

            const nameTag = currentCard.querySelector('h3');
            const itemName = nameTag ? nameTag.innerText.trim() : null;

            if (itemName) {
                const isNowLiked = toggleFavoriteStorage(itemName);
                updateHeartVisuals(this, isNowLiked);
                reorderMenuCards(currentCard.parentNode);
            }
        });
    });

    // ==========================================
    // PART 7: FEEDBACK / STAR RATING (Inayos para sa Modal)
    // ==========================================
    const stars = document.querySelectorAll('.stars i, .stars-input i');
    const submitBtn = document.querySelector('.btn-submit');
    const feedbackText = document.querySelector('textarea');
    let currentRating = 0; 

    // Logic para sa kulay ng stars habang nag-re-rate
    if (stars.length > 0) {
        stars.forEach((star, clickedIndex) => {
            star.addEventListener('click', () => {
                currentRating = clickedIndex + 1; 
                stars.forEach(s => {
                    s.classList.remove('fa-solid', 'active');
                    s.classList.add('fa-regular');
                });
                stars.forEach((s, index) => {
                    if (index <= clickedIndex) {
                        s.classList.replace('fa-regular', 'fa-solid');
                        s.classList.add('active');
                    }
                });
            });
        });
    }

    // ==========================================
    // PART 8 & 9: CHECKOUT PAGE LOGIC & INTERACTIONS
    // ==========================================
    const checkoutContainer = document.getElementById('order-items-container');
    if (checkoutContainer) {
        loadCheckoutItems();
    }

    const deliveryBtns = document.querySelectorAll('.opt-btn');
    const paymentBtns = document.querySelectorAll('.pay-btn');
    const addressSection = document.getElementById('address-info-section');
    const addressInputs = addressSection ? addressSection.querySelectorAll('input, textarea') : [];

    if (deliveryBtns.length > 0) {
        deliveryBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault(); 
                deliveryBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                if (this.innerText.includes('Pick Up')) {
                    if(addressSection) {
                        addressSection.style.display = 'none'; 
                        addressInputs.forEach(input => input.removeAttribute('required'));
                    }
                } else {
                    if(addressSection) {
                        addressSection.style.display = 'block';
                        addressInputs.forEach(input => {
                            if(input.tagName !== 'TEXTAREA') input.setAttribute('required', 'true');
                        });
                    }
                }
                recalculateCheckout(); 
            });
        });
    }

    if (paymentBtns.length > 0) {
        paymentBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                paymentBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }

    const placeOrderBtn = document.getElementById('placeOrderBtn');
    if (placeOrderBtn) {
        placeOrderBtn.addEventListener('click', function(e) {
            let cart = JSON.parse(localStorage.getItem('myCart')) || [];
            
            if(cart.length === 0) {
                e.preventDefault(); alert("Your cart is empty!"); return;
            }

            const fname = document.getElementById('fname').value;
            const lname = document.getElementById('lname').value;
            const phone = document.getElementById('phone').value;

            if(!fname || !lname || !phone) {
                // Hahayaan ang HTML5 validation na lumabas
                return; 
            }

            const activeDeliveryBtn = document.querySelector('.opt-btn.active');
            const isDelivery = activeDeliveryBtn && activeDeliveryBtn.innerText.includes('Delivery');
            
            if (isDelivery) {
                const addressInfo = document.getElementById('address-info-section');
                let isValid = true;
                addressInfo.querySelectorAll('input[required]').forEach(input => {
                    if (input.value.trim() === "") {
                        isValid = false; input.style.borderColor = "red"; 
                    } else { input.style.borderColor = "#FFE0B2"; }
                });
                if (!isValid) { e.preventDefault(); alert("Please fill in all delivery address details."); return; }
            }

            const activePayment = document.querySelector('.pay-btn.active');
            if (!activePayment) { e.preventDefault(); alert("Please select a payment method."); return; }

            // Bago mag-redirect ang PHP, linisin ang cart sa local storage
            localStorage.removeItem('myCart'); 
        });
    }

    // ==========================================
    // PART 10: CART PAGE BUTTON
    // ==========================================
    const proceedCheckoutBtn = document.getElementById('btn-proceed-checkout');
    if(proceedCheckoutBtn) {
        proceedCheckoutBtn.addEventListener('click', function() {
            proceedToCheckout();
        });
    }

    // ==========================================
    // PART 11: ORDER CONFIRMATION PAGE LOGIC
    // ==========================================
    const statusContainer = document.getElementById('status-details-container');
    if (statusContainer) {
        loadOrderStatus();
    }

    // ==========================================
    // PART 12: LIVE TRACKING (INTERACTIVE MAP)
    // ==========================================
    const mapContainer = document.getElementById('map');
    if (mapContainer) {
        var umakLat = 14.5637;
        var umakLng = 121.0563;
        var map = L.map('map', {
            center: [umakLat, umakLng],
            zoom: 15,
            dragging: true,
            touchZoom: true,
            scrollWheelZoom: true,
            doubleClickZoom: true
        });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        var riderIcon = L.icon({
            iconUrl: 'https://cdn-icons-png.flaticon.com/512/3063/3063822.png', 
            iconSize: [60, 60],
            iconAnchor: [30, 30]
        });
        var homeIcon = L.icon({
            iconUrl: 'https://cdn-icons-png.flaticon.com/512/1946/1946488.png',
            iconSize: [40, 40],
            iconAnchor: [20, 40]
        });
        L.marker([umakLat, umakLng], {icon: riderIcon}).addTo(map).bindPopup("<b>Rider Location</b>").openPopup(); 
        L.marker([14.5650, 121.0580], {icon: homeIcon}).addTo(map).bindPopup("<b>Delivery Address</b>");
        setTimeout(function() { map.invalidateSize(); }, 500);
    }


    // ==========================================
    // PART 14: HAMBURGER SIDE MENU TOGGLE
    // ==========================================
    const hamburgerBtn = document.getElementById('hamburger-btn');
    const closeBtn = document.getElementById('close-btn');
    const sideNav = document.getElementById('side-nav');
    const overlay = document.getElementById('overlay');

    if (hamburgerBtn && sideNav && overlay) {
        hamburgerBtn.addEventListener('click', () => {
            sideNav.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
        const closeMenu = () => {
            sideNav.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        };
        closeBtn.addEventListener('click', closeMenu);
        overlay.addEventListener('click', closeMenu);
    }

    // ==========================================
    // PART 15: AUTH UI & PAGE CONTROLLER
    // ==========================================

    if (window.location.pathname.includes('homepage.php')) {
        localStorage.setItem('isLoggedIn', 'true');
    }

    function updateAuthButton() {
        const authBtn = document.getElementById('auth-btn'); 
        const sideAuthBtn = document.getElementById('side-auth-btn'); 
        const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';

        if (isLoggedIn) {
            if (authBtn) {
                authBtn.innerHTML = '<i class="fa-solid fa-right-from-bracket"></i> <span class="btn-text">Logout</span>';
                authBtn.href = "#";
                authBtn.style.backgroundColor = "#333";
                authBtn.onclick = function(e) {
                    e.preventDefault();
                    if(confirm("Kabayan, sigurado ka bang gusto mong mag-logout?")) handleLogout();
                };
            }
            if (sideAuthBtn) {
                sideAuthBtn.innerText = "Logout";
                sideAuthBtn.onclick = function(e) {
                    e.preventDefault();
                    handleLogout();
                };
            }
        }
    }
    updateAuthButton();

    const status = localStorage.getItem('isLoggedIn');
    const path = window.location.pathname;

    if (status === 'true' && (path.includes('index.php') || path.endsWith('/'))) {
        window.location.href = "homepage.php";
    } else if (status !== 'true' && path.includes('homepage.php')) {
        window.location.href = "index.php";
    }

    document.addEventListener('click', function(e) {
        if (e.target && (e.target.id === 'btn-proceed-checkout' || e.target.closest('#btn-proceed-checkout'))) {
            if (localStorage.getItem('isLoggedIn') !== 'true') {
                e.stopImmediatePropagation();
                e.preventDefault();
                alert("Kabayan, kailangan mo munang mag-login para makapag-order.");
                window.location.href = "login.html";
            }
        }
    }, true);

}); 


// ==========================================
// GLOBAL FUNCTIONS
// ==========================================

function handleLogout() {
    localStorage.removeItem('isLoggedIn');
    window.location.href = "index.php"; 
}

function proceedToCheckout() {
    let cart = JSON.parse(localStorage.getItem('myCart')) || [];
    if(cart.length === 0) {
        alert("Your cart is empty! Please add items first.");
    } else {
        window.location.href = "checkout.php"; 
    }
}

function addToCart(itemName, itemPrice, itemImage) {
    let cart = JSON.parse(localStorage.getItem('myCart')) || [];
    const existingItem = cart.find(item => item.name === itemName);
    if (existingItem) {
        existingItem.qty += 1;
    } else {
        let finalImage = itemImage || 'https://cdn-icons-png.flaticon.com/512/706/706164.png';
        cart.push({ name: itemName, price: itemPrice, qty: 1, img: finalImage });
    }
    localStorage.setItem('myCart', JSON.stringify(cart));
    updateBadgeCount();
    showToast();
    const cartListElement = document.getElementById('cart-items-list');
    if (cartListElement) renderCartPage(); 
}

function updateQuantity(itemName, change) {
    let cart = JSON.parse(localStorage.getItem('myCart')) || [];
    const item = cart.find(i => i.name === itemName);
    if (item) {
        item.qty += change;
        if (item.qty <= 0) cart = cart.filter(i => i.name !== itemName);
    }
    localStorage.setItem('myCart', JSON.stringify(cart));
    updateBadgeCount();
    const cartListElement = document.getElementById('cart-items-list');
    if (cartListElement) renderCartPage(); 
    const checkoutContainer = document.getElementById('order-items-container');
    if (checkoutContainer) loadCheckoutItems(); 
}

function renderCartPage() {
    const cartListElement = document.getElementById('cart-items-list');
    const subtotalEl = document.getElementById('subtotal-display');
    const totalEl = document.getElementById('total-display');
    if (!cartListElement) return; 
    let cart = JSON.parse(localStorage.getItem('myCart')) || [];
    cartListElement.innerHTML = "";
    let grandTotal = 0;
    if (cart.length === 0) {
        cartListElement.innerHTML = `<div class="empty-state" style="text-align: center; color: #ccc; margin-top: 40px;"><i class="fa-solid fa-basket-shopping" style="font-size: 3rem; margin-bottom: 10px;"></i><p>No items yet</p></div>`;
    } else {
        cart.forEach(item => {
            const itemTotal = item.price * item.qty;
            grandTotal += itemTotal;
            const safeName = item.name.replace(/'/g, "\\'"); 
            cartListElement.innerHTML += `
                <div class="cart-item" style="display: flex; align-items: center; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px dashed #eee;">
                    <div class="cart-item-info" style="flex: 2;">
                        <h4 style="font-size: 0.9rem; margin-bottom: 2px;">${item.name}</h4>
                        <span class="u-price" style="font-size: 0.8rem; color: #888;">@ ${item.price.toFixed(2)}</span>
                    </div>
                    <div class="cart-controls" style="flex: 1; display: flex; justify-content: center;">
                        <div style="display: flex; align-items: center; gap: 8px; background: #f1f1f1; border-radius: 20px; padding: 2px 8px;">
                            <button class="btn-qty" onclick="updateQuantity('${safeName}', -1)" style="border: none; background: none; cursor: pointer;"><i class="fa-solid fa-minus"></i></button>
                            <span class="qty-display" style="font-weight: bold; font-size: 0.9rem; width: 20px; text-align: center;">${item.qty}</span>
                            <button class="btn-qty" onclick="updateQuantity('${safeName}', 1)" style="border: none; background: none; cursor: pointer;"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="item-total-price" style="flex: 1; text-align: right; font-weight: bold;">
                        ${itemTotal.toFixed(2)}
                    </div>
                </div>
            `;
        });
    }
    if(subtotalEl) subtotalEl.innerText = grandTotal.toFixed(2);
    if(totalEl) totalEl.innerText = grandTotal.toFixed(2);
}

function updateBadgeCount() {
    let cart = JSON.parse(localStorage.getItem('myCart')) || [];
    let totalQty = cart.reduce((sum, item) => sum + item.qty, 0);
    const badge = document.getElementById('cart-badge');
    const checkBadge = document.getElementById('checkout-cart-badge'); 
    if(badge) badge.innerText = totalQty;
    if(checkBadge) checkBadge.innerText = totalQty;
}

function showToast() {
    var x = document.getElementById("toast");
    if(x) {
        x.className = "show";
        setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
    }
}

function recalculateCheckout() {
    const shippingEl = document.getElementById('checkout-shipping');
    const totalEl = document.getElementById('checkout-total');
    const subtotalEl = document.getElementById('checkout-subtotal');
    const hiddenTotalEl = document.getElementById('final-total-hidden'); 
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    
    let cart = JSON.parse(localStorage.getItem('myCart')) || [];
    let subtotal = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
    
    const activeDeliveryBtn = document.querySelector('.opt-btn.active');
    const isDelivery = activeDeliveryBtn ? activeDeliveryBtn.innerText.includes('Delivery') : true;
    const shippingFee = isDelivery ? 45.00 : 0.00; 
    
    let grandTotal = subtotal + shippingFee;

    if(subtotalEl) subtotalEl.innerText = 'PHP ' + subtotal.toFixed(2);
    if(shippingEl) shippingEl.innerText = 'PHP ' + shippingFee.toFixed(2);
    if(totalEl) totalEl.innerText = 'PHP ' + grandTotal.toFixed(2);
    
    // In-update ang hidden input value para sa PHP database
    if(hiddenTotalEl) hiddenTotalEl.value = grandTotal;

    if (cart.length === 0 && placeOrderBtn) {
        placeOrderBtn.style.opacity = '0.5'; placeOrderBtn.style.cursor = 'not-allowed'; 
    } else if (placeOrderBtn) {
        placeOrderBtn.style.opacity = '1'; placeOrderBtn.style.cursor = 'pointer';
    }
}

function loadCheckoutItems() {
    let cart = JSON.parse(localStorage.getItem('myCart')) || [];
    const container = document.getElementById('order-items-container');
    if (!container) return;
    container.innerHTML = ''; 
    if (cart.length === 0) {
        container.innerHTML = '<p style="text-align:center; padding: 20px; color: #666;">Your cart is empty. <br> <a href="menu.php" style="color: orange; text-decoration: none;">Go back to Menu</a></p>';
    } else {
        cart.forEach(item => {
            let itemTotal = item.price * item.qty;
            let imageSrc = item.img ? item.img : 'https://cdn-icons-png.flaticon.com/512/706/706164.png';
            const safeName = item.name.replace(/'/g, "\\'");
            container.innerHTML += `
                <div class="summary-item">
                    <div class="item-img"><img src="${imageSrc}" alt="${item.name}" style="width: 70px; height: 70px; object-fit: cover; border-radius: 8px;"></div>
                    <div class="item-details">
                        <h4>${item.name}</h4>
                        <div class="qty-control" style="display:flex; align-items:center; gap:10px;">
                            <button type="button" class="qty-sm-btn" onclick="updateQuantity('${safeName}', -1)">-</button>
                            <span style="font-weight:bold;">${item.qty}</span>
                            <button type="button" class="qty-sm-btn" onclick="updateQuantity('${safeName}', 1)">+</button>
                        </div>
                    </div>
                    <div class="item-price"><span class="current-price">PHP ${itemTotal.toFixed(2)}</span></div>
                </div>
            `;
        });
    }
    recalculateCheckout();
}

function loadOrderStatus() {
    const orderData = JSON.parse(localStorage.getItem('latestOrder'));
    if (!orderData) return;
    const nameHero = document.getElementById('conf-name-hero');
    if(nameHero) nameHero.innerText = orderData.customerName;
    const orderIdEl = document.getElementById('conf-order-id');
    if(orderIdEl) orderIdEl.innerText = orderData.orderId;
    const itemsList = document.getElementById('conf-items-list');
    if(itemsList) {
        let subtotal = 0; itemsList.innerHTML = '';
        orderData.items.forEach(item => {
            let itemTotal = item.price * item.qty; subtotal += itemTotal;
            itemsList.innerHTML += `<div class="d-row item-row"><span class="label bold">${item.qty}x ${item.name}</span><div class="price-group"><span class="value bold">PHP ${itemTotal.toFixed(2)}</span></div></div>`;
        });
        let grandTotal = subtotal + orderData.shippingFee;
        const totalEl = document.getElementById('conf-total');
        if(totalEl) totalEl.innerText = 'PHP ' + grandTotal.toFixed(2);
    }
}

function reorderMenuCards(container) {
    const cards = Array.from(container.children).filter(child => 
        child.classList.contains('menu-card') || child.classList.contains('menu-card-scroll')
    );
    cards.sort((a, b) => {
        const nameA = a.querySelector('h3')?.innerText.trim();
        const nameB = b.querySelector('h3')?.innerText.trim();
        const isFavA = checkFavoriteStatus(nameA);
        const isFavB = checkFavoriteStatus(nameB);
        if (isFavA === isFavB) return parseInt(a.getAttribute('data-original-index')) - parseInt(b.getAttribute('data-original-index'));
        return isFavA ? -1 : 1;
    });
    cards.forEach(card => container.appendChild(card));
}

function toggleFavoriteStorage(name) {
    let favs = JSON.parse(localStorage.getItem('myFavorites')) || [];
    const index = favs.indexOf(name);
    let isLiked = false;
    if (index > -1) { favs.splice(index, 1); isLiked = false; }
    else { favs.push(name); isLiked = true; }
    localStorage.setItem('myFavorites', JSON.stringify(favs));
    return isLiked;
}

function checkFavoriteStatus(name) {
    let favs = JSON.parse(localStorage.getItem('myFavorites')) || [];
    return favs.includes(name);
}

function updateHeartVisuals(btn, isLiked) {
    const icon = btn.querySelector('i');
    if (isLiked) {
        btn.classList.add('active');
        btn.style.border = "2px solid #ff4757"; btn.style.backgroundColor = "#ffe0e6"; 
        if(icon) { icon.classList.replace('fa-regular', 'fa-solid'); icon.style.color = '#ff4757'; }
    } else {
        btn.classList.remove('active');
        btn.style.border = ""; btn.style.backgroundColor = ""; 
        if(icon) { icon.classList.replace('fa-solid', 'fa-regular'); icon.style.color = ""; }
    }
}

function loadFavoritesState() {
    const allFavBtns = document.querySelectorAll('.btn-fav, .btn-heart');
    allFavBtns.forEach(btn => {
        const card = btn.closest('.menu-card') || btn.closest('.menu-card-scroll');
        if (card) {
            const nameTag = card.querySelector('h3');
            if (nameTag) updateHeartVisuals(btn, checkFavoriteStatus(nameTag.innerText.trim()));
        }
    });
}

// MGA FUNCTIONS PARA SA RATINGS MODAL
function openReviewModal() {
    const modal = document.getElementById('reviewModal');
    if(modal) modal.style.display = 'flex';
}

function closeReviewModal() {
    const modal = document.getElementById('reviewModal');
    if(modal) modal.style.display = 'none';
}