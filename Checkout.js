/**
 * KAINAN NI ATE KABAYAN - Checkout.js (AUTOMATIC & DEBOUNCED VERSION)
 */

const STORE_LAT = 14.5547; 
const STORE_LNG = 121.0244;
const LALAMOVE_BASE_FARE = 49; 
const COD_FIXED_FEE = 50;      

// --- DEBOUNCE TIMER ---
let typingTimer;
const doneTypingInterval = 1000; 

function getActiveCartKey() {
    const userId = window.currentUserId || "";
    const userSpecificKey = userId ? 'cart_' + userId : null;
    if (localStorage.getItem('myCart')) return 'myCart';
    if (userSpecificKey && localStorage.getItem(userSpecificKey)) return userSpecificKey;
    return 'cart_guest';
}

function calculateDistance(lat1, lon1, lat2, lon2) {
    if (!lat2 || !lon2) return 0;
    const R = 6371; 
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
              Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c; 
}

// --- 1. GLOBAL FUNCTIONS ---

window.placeOrder = function() {
    const isDelivery = document.getElementById('btn-delivery')?.classList.contains('active');
    const custLat = document.getElementById('cust_lat')?.value;
    const custLng = document.getElementById('cust_lng')?.value;

    if (isDelivery && (!custLat || !custLng || parseFloat(custLat) === STORE_LAT)) {
        alert("Kabayan, paki-pin muna ang iyong exact location sa mapa para makuha ang tamang delivery fee.");
        return;
    }

    const activePayBtn = document.querySelector('.pay-btn.active');
    const paymentMethod = activePayBtn ? activePayBtn.getAttribute('data-method') : 'Cash';
    const totalEl = document.getElementById('summary-total');
    if (!totalEl) return alert("Error: Total price not found.");
    
    const finalTotal = parseFloat(totalEl.innerText.replace(/[^\d.-]/g, ''));
    const address = document.getElementById('display-text')?.innerText || "No address provided";
    const note = document.getElementById('delivery-note')?.value || "";

    const activeKey = getActiveCartKey();
    const cart = JSON.parse(localStorage.getItem(activeKey)) || [];
    if (cart.length === 0) return alert("Walang laman ang cart!");

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = (paymentMethod === 'Cash') ? 'process_cash_order.php' : 'create_payment.php'; 

    const data = {
        'total_price': finalTotal,
        'amount': finalTotal, 
        'payment_method': paymentMethod,
        'address': address,
        'notes': note,
        'order_type': isDelivery ? 'Delivery' : 'Pick Up', // Tanging order_type na lang ang ipapadala[cite: 12]
        'cart_data': JSON.stringify(cart)
    };

    for (const key in data) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = data[key];
        form.appendChild(input);
    }
    document.body.appendChild(form);
    form.submit(); 
};

window.changeQtyCheckout = (name, amount) => {
    const activeKey = getActiveCartKey();
    let cart = JSON.parse(localStorage.getItem(activeKey)) || [];
    const idx = cart.findIndex(i => i.name === name);
    if (idx > -1) {
        cart[idx].qty = parseInt(cart[idx].qty || 0) + amount;
        if (cart[idx].qty <= 0) cart.splice(idx, 1);
    }
    localStorage.setItem(activeKey, JSON.stringify(cart));
    if (typeof window.triggerRender === 'function') window.triggerRender();
};

// --- 2. UI & CALCULATION LOGIC ---

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('edit-textarea')?.addEventListener('input', function() {
        clearTimeout(typingTimer);
        const typedAddress = this.value;
        
        if (typedAddress.length > 8) {
            typingTimer = setTimeout(() => {
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(typedAddress)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.length > 0) {
                            const lat = parseFloat(data[0].lat);
                            const lon = parseFloat(data[0].lon);
                            
                            if (typeof marker !== 'undefined') {
                                marker.setLatLng([lat, lon]);
                                map.setView([lat, lon], 17);
                            }
                            document.getElementById('cust_lat').value = lat;
                            document.getElementById('cust_lng').value = lon;
                            window.triggerRender();
                        }
                    });
            }, doneTypingInterval);
        }
    });

    const renderSummary = () => {
        const activeKey = getActiveCartKey();
        const cart = JSON.parse(localStorage.getItem(activeKey)) || [];
        const container = document.getElementById('order-summary-list');
        if (!container) return;

        container.innerHTML = '';
        let subtotal = 0;
        cart.forEach(item => {
            const price = parseFloat(String(item.price).replace(/[^\d.-]/g, '')) || 0;
            const qty = parseInt(item.qty) || 0;
            subtotal += price * qty;
            container.innerHTML += `
                <div class="summary-item" style="display:flex; gap:15px; margin-bottom:15px; align-items:center; background:#fff; padding:12px; border-radius:15px; border:1px solid #eee;">
                    <img src="${item.img}" style="width:50px; height:50px; border-radius:10px; object-fit:cover;">
                    <div style="flex:1;">
                        <h4 style="font-size:0.9rem; margin:0;">${item.name}</h4>
                        <div style="display:flex; align-items:center; gap:10px; margin-top:5px;">
                             <button onclick="changeQtyCheckout('${item.name.replace(/'/g, "\\'")}', -1)" style="border:none; cursor:pointer;">-</button>
                             <span>${qty}</span>
                             <button onclick="changeQtyCheckout('${item.name.replace(/'/g, "\\'")}', 1)" style="border:none; cursor:pointer;">+</button>
                        </div>
                    </div>
                    <span style="font-weight:800;">₱${(price * qty).toFixed(2)}</span>
                </div>`;
        });

        const isDelivery = document.getElementById('btn-delivery')?.classList.contains('active');
        const paymentMethod = document.querySelector('.pay-btn.active')?.getAttribute('data-method') || 'Cash';
        
        let lalamoveFee = 0;
        let codServiceFee = 0;

        if (isDelivery) {
            const lat = parseFloat(document.getElementById('cust_lat')?.value);
            const lng = parseFloat(document.getElementById('cust_lng')?.value);
            const distance = calculateDistance(STORE_LAT, STORE_LNG, lat, lng);

            lalamoveFee = LALAMOVE_BASE_FARE;
            if (distance > 0) {
                if (distance <= 5) lalamoveFee += distance * 6; 
                else lalamoveFee += (5 * 6) + ((distance - 5) * 5); 
            }

            if (paymentMethod === 'Cash') {
                codServiceFee = COD_FIXED_FEE; 
                document.getElementById('cod-fee-row').style.display = 'flex';
            } else {
                document.getElementById('cod-fee-row').style.display = 'none';
            }
        } else {
            document.getElementById('cod-fee-row').style.display = 'none';
        }

        let finalVat = subtotal * 0.12;
        let finalTotal = subtotal + lalamoveFee + codServiceFee + finalVat;

        if(document.getElementById('summary-subtotal')) document.getElementById('summary-subtotal').innerText = `PHP ${subtotal.toFixed(2)}`;
        if(document.getElementById('vat-display')) document.getElementById('vat-display').innerText = `PHP ${finalVat.toFixed(2)}`;
        if(document.getElementById('delivery-fee-amount')) document.getElementById('delivery-fee-amount').innerText = `PHP ${lalamoveFee.toFixed(2)}`;
        if(document.getElementById('cod-fee-amount')) document.getElementById('cod-fee-amount').innerText = `PHP ${codServiceFee.toFixed(2)}`;
        if(document.getElementById('summary-total')) document.getElementById('summary-total').innerText = finalTotal.toFixed(2);
    };

    window.triggerRender = renderSummary;
    document.querySelectorAll('.opt-btn, .pay-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const cls = this.classList.contains('opt-btn') ? '.opt-btn' : '.pay-btn';
            document.querySelectorAll(cls).forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            renderSummary();
        });
    });

    renderSummary();
});

const addressModal = document.getElementById('figmaModal');
document.getElementById('open-address-modal')?.addEventListener('click', () => addressModal?.classList.add('active'));
document.getElementById('cancel-modal')?.addEventListener('click', () => addressModal?.classList.remove('active'));