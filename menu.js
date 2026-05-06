document.addEventListener('DOMContentLoaded', function() {

    // ==========================================
    // PART 1: HAMBURGER & PROFILE SIDE-NAV
    // ==========================================
    const hamburgerBtn = document.getElementById('hamburger-btn');
    const closeBtn = document.getElementById('close-btn');
    const sideNav = document.getElementById('side-nav');
    const overlay = document.getElementById('overlay');

    const toggleMenu = (isOpen) => {
        if (sideNav && overlay) {
            sideNav.classList.toggle('active', isOpen);
            overlay.classList.toggle('active', isOpen);
            document.body.style.overflow = isOpen ? 'hidden' : 'auto';
        }
    };

    hamburgerBtn?.addEventListener('click', () => toggleMenu(true));
    closeBtn?.addEventListener('click', () => toggleMenu(false));
    overlay?.addEventListener('click', () => toggleMenu(false));

    // ==========================================
    // PART 2: CATEGORY FILTERING LOGIC
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
    const menuGrid = document.getElementById('menuGrid');
    const menuCards = document.querySelectorAll('.menu-card');

    function renderCategories() {
        if (!categoryContainer) return;
        categoryContainer.innerHTML = '';
        categories.forEach(cat => {
            const btn = document.createElement('div');
            btn.className = `category-card ${cat.id === 'all' ? 'active' : ''}`;
            btn.innerHTML = `
                <div class="cat-icon">${cat.icon}</div>
                <span class="cat-name">${cat.name}</span>
                <span class="cat-count">${cat.count}</span>
            `;
            btn.addEventListener('click', () => {
                document.querySelectorAll('.category-card').forEach(c => c.classList.remove('active'));
                btn.classList.add('active');
                filterMenuCards(cat.id);
            });
            categoryContainer.appendChild(btn);
        });
    }

    function filterMenuCards(category) {
        menuCards.forEach(card => {
            const cardCategory = card.getAttribute('data-category');
            card.style.display = (category === 'all' || cardCategory === category) ? 'flex' : 'none';
        });
        reorderMenuCards(); 
    }

    // ==========================================
    // PART 3: LIVE SEARCH (FB STALKING STYLE) - FIXED!
    // ==========================================
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');

    searchInput?.addEventListener('input', function(e) {
        const searchText = e.target.value.toLowerCase();
        
        // 1. Linisin ang dropdown content
        if (searchResults) searchResults.innerHTML = ''; 

        // 2. Kapag walang text, itago ang dropdown at ipakita lahat ng cards
        if (searchText.length < 1) {
            if (searchResults) searchResults.style.display = 'none';
            menuCards.forEach(card => card.style.display = 'flex');
            return;
        }

        let hasResults = false;

        menuCards.forEach(card => {
            const title = card.querySelector('h3').innerText;
            const imgSrc = card.querySelector('img').src;
            const price = card.querySelector('.price').innerText;

            if (title.toLowerCase().includes(searchText)) {
                hasResults = true;
                if (searchResults) searchResults.style.display = 'block';

                // Gagawa ng search item box na may image (FB Stalking Look)
                const item = document.createElement('div');
                item.className = 'search-item';
                item.innerHTML = `
                    <img src="${imgSrc}" alt="${title}">
                    <div class="item-info">
                        <span class="item-name">${title}</span>
                        <span class="item-price">${price}</span>
                    </div>
                `;

                // Logic kapag clinick ang item sa dropdown
                item.addEventListener('click', () => {
                    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    // Highlight effect sa card
                    card.style.boxShadow = "0 0 25px var(--primary-orange)";
                    setTimeout(() => card.style.boxShadow = "", 2000);
                    
                    if (searchResults) searchResults.style.display = 'none';
                    searchInput.value = title;
                });

                if (searchResults) searchResults.appendChild(item);
                
                // I-filter din ang grid sa likod
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });

        // Itago kung walang nahanap
        if (!hasResults && searchResults) searchResults.style.display = 'none';
    });

    // Isara ang search results kapag pinindot ang labas
    document.addEventListener('click', (e) => {
        if (!searchInput?.contains(e.target) && !searchResults?.contains(e.target)) {
            if (searchResults) searchResults.style.display = 'none';
        }
    });

    // ==========================================
    // PART 4: FAVORITE (HEART) & PRIORITY SORTING
    // ==========================================
    menuCards.forEach((card, index) => {
        card.setAttribute('data-original-index', index);
    });

    function reorderMenuCards() {
        const cardsArray = Array.from(menuCards);
        cardsArray.sort((a, b) => {
            const isFavA = a.querySelector('.btn-fav').classList.contains('active');
            const isFavB = b.querySelector('.btn-fav').classList.contains('active');
            const indexA = parseInt(a.getAttribute('data-original-index'));
            const indexB = parseInt(b.getAttribute('data-original-index'));

            if (isFavA === isFavB) return indexA - indexB;
            return isFavA ? -1 : 1;
        });
        cardsArray.forEach(card => menuGrid.appendChild(card));
    }

    document.addEventListener('click', function(e) {
        const favBtn = e.target.closest('.btn-fav');
        if (favBtn) {
            e.preventDefault();
            favBtn.classList.toggle('active');
            const icon = favBtn.querySelector('i');
            if (favBtn.classList.contains('active')) {
                icon.classList.replace('fa-regular', 'fa-solid');
            } else {
                icon.classList.replace('fa-solid', 'fa-regular');
            }
            reorderMenuCards();
        }
    });

    // Initial Load
    renderCategories();
    updateBadgeCount();
});

// ==========================================
// PART 5: GLOBAL CART FUNCTIONS (FIXED SYNC)
// ==========================================

function getActiveCartKey() {
    return (typeof cartKey !== 'undefined') ? cartKey : 'cart_guest';
}

function addToCart(itemName, itemPrice, itemImage) {
    const activeKey = getActiveCartKey();
    let cart = JSON.parse(localStorage.getItem(activeKey)) || [];
    
    const existingItem = cart.find(item => item.name === itemName);
    if (existingItem) {
        existingItem.qty += 1;
    } else {
        cart.push({ name: itemName, price: itemPrice, qty: 1, img: itemImage });
    }
    
    localStorage.setItem(activeKey, JSON.stringify(cart));
    localStorage.setItem('myCart', JSON.stringify(cart));

    if (activeKey !== 'cart_guest') {
        const formData = new FormData();
        formData.append('name', itemName);
        formData.append('price', itemPrice);
        formData.append('img', itemImage);

        fetch('add_to_cart.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                updateBadgeCount();
                console.log("Synced to database: " + itemName);
            }
        })
        .catch(err => console.error("Database sync error:", err));
    } else {
        updateBadgeCount();
    }

    showToast(`Added ${itemName} to cart!`);
}

function updateBadgeCount() {
    const activeKey = getActiveCartKey();

    if (activeKey !== 'cart_guest') {
        fetch('get_cart_count.php')
        .then(res => res.json())
        .then(data => {
            const badge = document.getElementById('cart-badge');
            if (badge) {
                const count = data.total_qty || 0;
                badge.innerText = count;
                badge.style.display = count > 0 ? 'flex' : 'none';
            }
        })
        .catch(() => {
            updateBadgeFromLocal(activeKey);
        });
    } 
    else {
        updateBadgeFromLocal(activeKey);
    }
}

function updateBadgeFromLocal(key) {
    let cart = JSON.parse(localStorage.getItem(key)) || [];
    // FIX: parseInt para siguradong tamang bilang at hindi maging "011"
    let totalQty = cart.reduce((sum, item) => sum + parseInt(item.qty || 0), 0);
    const badge = document.getElementById('cart-badge');
    if (badge) {
        badge.innerText = totalQty;
        badge.style.display = totalQty > 0 ? 'flex' : 'none';
    }
}

function showToast(message) {
    const toast = document.getElementById('toast');
    if (toast) {
        toast.innerText = message;
        toast.style.visibility = 'visible';
        toast.classList.add('show');
        setTimeout(() => {
            toast.style.visibility = 'hidden';
            toast.classList.remove('show');
        }, 3000);
    }
}