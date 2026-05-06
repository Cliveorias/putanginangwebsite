// ==========================================
// PART 1: GLOBAL FUNCTIONS (VISIBLE TO HTML)
// ==========================================

/**
 * Kumokontrol sa paglipat ng sections sa sidebar.
 * Ginawang window function para siguradong accessible sa onclick.
 */
window.showSection = function(sectionId, btn) {
    const desc = document.getElementById('section-desc');
    if (!desc) return;

    // 1. Itago ang lahat ng cards at sections muna
    document.querySelectorAll('.active-orders').forEach(c => c.style.display = 'none');
    document.querySelectorAll('.previous-orders').forEach(c => c.style.display = 'none');
    document.querySelectorAll('.order-section').forEach(s => s.style.display = 'none');
    
    // 2. Alisin ang 'active' class sa lahat ng sidebar buttons
    document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
    
    // 3. I-activate ang pinindot na button
    if (btn) btn.classList.add('active');

    // 4. Ipakita ang tamang content base sa pinindot
    if (sectionId === 'active-orders') {
        document.querySelectorAll('.active-orders').forEach(c => c.style.display = 'flex');
        desc.innerText = "Here's your active orders at Kainan ni Ate Kabayan.";
    } else if (sectionId === 'previous-orders') {
        document.querySelectorAll('.previous-orders').forEach(c => c.style.display = 'flex');
        desc.innerText = "Here's your previous orders at Kainan ni Ate Kabayan.";
    } else {
        const targetSection = document.getElementById(sectionId);
        if (targetSection) targetSection.style.display = 'block';
        
        if (sectionId === 'my-reviews') desc.innerText = "Your history of feedback and ratings.";
        else if (sectionId === 'contact-us') desc.innerText = "Need help? Reach out to us.";
    }
};

/**
 * Binubuksan ang Breakdown Modal
 */
window.openBreakdown = function(orderId, orderDate, orderTotal) {
    const modal = document.getElementById('breakdownModal');
    if (!modal) return;

    document.getElementById('modal-order-id').innerText = "#" + orderId;
    document.getElementById('modal-date').innerHTML = orderDate.replace(' - ', '<br>');
    
    // Placeholder list
    document.getElementById('modal-items-list').innerHTML = `
        Gotong Batangas &nbsp;&nbsp;&nbsp; PHP 150.00<br>
        Porksilog &nbsp;&nbsp;&nbsp; PHP 130.00<br>
        Lugaw Lechon &nbsp;&nbsp;&nbsp; PHP 95.00<br>
        Coca Cola Mismo &nbsp;&nbsp;&nbsp; PHP 30.00
    `;
    
    document.getElementById('modal-total').innerText = "PHP " + parseFloat(orderTotal).toFixed(2);
    modal.style.display = 'flex';
};

window.closeBreakdown = function() {
    const modal = document.getElementById('breakdownModal');
    if (modal) modal.style.display = 'none';
};

// ==========================================
// PART 2: PAGE INITIALIZATION
// ==========================================
document.addEventListener('DOMContentLoaded', () => {

    const hamburgerBtn = document.getElementById('hamburger-btn');
    const sideNav = document.getElementById('side-nav');
    const overlay = document.getElementById('overlay');

    // Sidebar Mobile Toggle logic
    const toggleMenu = (isOpen) => {
        if (sideNav) sideNav.classList.toggle('active', isOpen);
        if (overlay) overlay.classList.toggle('active', isOpen);
        document.body.style.overflow = isOpen ? 'hidden' : 'auto';
    };

    hamburgerBtn?.addEventListener('click', () => toggleMenu(true));
    document.getElementById('close-btn')?.addEventListener('click', () => toggleMenu(false));
    overlay?.addEventListener('click', () => toggleMenu(false));

    // Isara ang modal kapag clinick ang labas nito
    window.onclick = function(event) {
        const modal = document.getElementById('breakdownModal');
        if (event.target === modal) closeBreakdown();
    };

    // Update Badge Count (Anti-011 Fix)
    const updateBadge = () => {
        const badge = document.getElementById('cart-badge');
        if (!badge) return;
        const key = (typeof cartKey !== 'undefined') ? cartKey : 'myCart';
        const cart = JSON.parse(localStorage.getItem(key)) || [];
        const total = cart.reduce((sum, item) => sum + parseInt(item.qty || 0), 0);
        badge.innerText = total;
        badge.style.display = total > 0 ? 'flex' : 'none';
    };

    updateBadge();
});