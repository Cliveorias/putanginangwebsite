document.addEventListener('DOMContentLoaded', () => {
    const hamburgerBtn = document.getElementById('hamburger-btn');
    const closeBtn = document.getElementById('close-btn');
    const sideNav = document.getElementById('side-nav');
    const overlay = document.getElementById('overlay');

    // Function to open menu
    const openMenu = () => {
        sideNav.classList.add('active');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden'; 
    };

    // Function to close menu
    const closeMenu = () => {
        sideNav.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = 'auto'; 
    };

    hamburgerBtn?.addEventListener('click', openMenu);
    closeBtn?.addEventListener('click', closeMenu);
    overlay?.addEventListener('click', closeMenu);

    // Initial Badge update (kung may laman ang cart)
    const updateBadge = () => {
        const cart = JSON.parse(localStorage.getItem('myCart')) || [];
        const total = cart.reduce((sum, item) => sum + item.qty, 0);
        const badge = document.getElementById('cart-badge');
        if (badge) badge.innerText = total;
    };
    updateBadge();
});