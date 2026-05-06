/**
 * Kainan ni Ate Kabayan - Kitchen Logic
 * Handles real-time order fetching, 3-step button transitions, and countdowns.
 */

let currentFilter = 'all';
let lastOrderCount = 0;

/**
 * Hinihila ang mga orders mula sa database gamit ang fetch_orders.php.
 */
async function fetchOrders() {
    try {
        const response = await fetch(`fetch_orders.php?filter=${currentFilter}`);
        const orders = await response.json();
        const grid = document.getElementById('order-grid');
        
        // Sound alert para sa bagong orders
        const pendingCount = orders.filter(o => o.status === 'Pending').length;
        if (pendingCount > lastOrderCount) {
            document.getElementById('order-sound').play();
        }
        lastOrderCount = pendingCount;

        grid.innerHTML = '';

        orders.forEach(order => {
            const card = document.createElement('div');
            // Status class para sa color coding (Blue, Orange, Green)
            const statusClass = (order.status === 'Pending') ? 'incoming' : 
                                (order.status === 'Preparing') ? 'preparing' : 'ready';
            card.className = `order-card ${statusClass}`;
            
            // --- DYNAMIC BUTTON LOGIC (3-STEP WORKFLOW) ---
            let actionSection = '';
            
            if (order.status === 'Pending') {
                // Step 1: Input minutes at Start Cooking button
                actionSection = `
                    <div class="action-zone">
                        <label>Cooking Time (Minutes):</label>
                        <div class="input-group">
                            <input type="number" id="time-${order.id}" min="1" placeholder="e.g. 20">
                            <button class="btn-start" onclick="startPrep(${order.id})">
                                <i class="fa-solid fa-fire-burner"></i> Start Cooking
                            </button>
                        </div>
                    </div>`;
            } else if (order.status === 'Preparing') {
                // Step 2: Timer display, Extend, at Finish Preparing button
                actionSection = `
                    <div class="timer-container">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <span class="timer-display" 
                              id="timer-${order.id}"
                              data-start="${order.prep_start_unix}" 
                              data-duration="${order.prep_time}">
                            Initializing...
                        </span>
                    </div>
                    <div class="btn-row">
                        <button class="btn-extend" onclick="alert('Time extended!')">Extend Time</button>
                        <button class="btn-finish" onclick="finishPrep(${order.id})">Finish Preparing</button>
                    </div>`;
            } else if (order.status === 'Ready for Dispatch') {
                // Step 3: Tracking Link input at Dispatch button
                actionSection = `
                    <div class="tracking-zone">
                        <label><i class="fa-solid fa-truck-ramp-box"></i> Lalamove Tracking Link:</label>
                        <div class="input-group">
                            <input type="text" id="link-${order.id}" 
                                   value="${order.tracking_link || ''}" 
                                   placeholder="Paste Lalamove URL here...">
                            <button class="btn-update" onclick="dispatchOrder(${order.id})">
                                <i class="fa-solid fa-share-from-square"></i> Dispatch
                            </button>
                        </div>
                    </div>`;
            }

            card.innerHTML = `
                <div class="card-header">
                    <div class="order-id">#${order.id}</div>
                    <div class="customer-name">${order.full_name}</div>
                    <span class="status-badge">${order.status}</span>
                </div>

                <div class="order-body">
                    <p class="items-list"><strong>Items:</strong> ${order.order_items}</p>
                    <p class="order-type"><strong>Type:</strong> ${order.order_type}</p>
                    <div class="notes-box"><strong>Notes:</strong> ${order.notes || 'None'}</div>
                </div>
                
                <div class="action-container">
                    ${actionSection}
                </div>
            `;
            grid.appendChild(card);
        });

        updateTimers(); // I-trigger ang countdown para sa Preparing orders
    } catch (error) {
        console.error("Error fetching orders:", error);
    }
}

/**
 * Step 1: Start Cooking (Pending -> Preparing)
 */
async function startPrep(orderId) {
    const mins = document.getElementById(`time-${orderId}`).value;
    if (!mins || mins <= 0) {
        alert("Paki-lagay kung ilang minuto ang lulutuin.");
        return;
    }
    await sendAction(orderId, 'start_prep', { mins });
}

/**
 * Step 2: Finish Preparing (Preparing -> Ready for Dispatch)
 */
async function finishPrep(orderId) {
    if (!confirm(`Tapos na ba talagang lutuin ang Order #${orderId}?`)) return;
    await sendAction(orderId, 'finish_prep'); // Kailangan ng case na 'finish_prep' sa process_kitchen.php
}

/**
 * Step 3: Dispatch (Ready for Dispatch -> On the Way)
 */
async function dispatchOrder(orderId) {
    const link = document.getElementById(`link-${orderId}`).value;
    if (!link) {
        alert("Paki-paste muna ang Lalamove tracking link.");
        return;
    }
    await sendAction(orderId, 'handover', { link });
}

/**
 * Helper function para sa AJAX calls
 */
async function sendAction(id, action, extras = {}) {
    const formData = new FormData();
    formData.append('id', id);
    formData.append('action', action);
    for (let key in extras) formData.append(key, extras[key]);

    await fetch('update_kitchen_status.php', { method: 'POST', body: formData });
    fetchOrders(); // Refresh ang dashboard
}

/**
 * Countdown Timer Logic
 */
function updateTimers() {
    document.querySelectorAll('.timer-display').forEach(timer => {
        const start = parseInt(timer.getAttribute('data-start'));
        const duration = parseInt(timer.getAttribute('data-duration'));
        if (!start || !duration) return;

        const endTime = (start + (duration * 60)) * 1000;
        const now = new Date().getTime();
        const distance = endTime - now;

        if (distance < 0) {
            timer.innerText = "Luto na!";
            timer.style.color = "#38b000";
        } else {
            const m = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const s = Math.floor((distance % (1000 * 60)) / 1000);
            timer.innerText = `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
        }
    });
}

function setFilter(filter) {
    currentFilter = filter;
    fetchOrders();
}

// Auto-refresh at Timers
setInterval(updateTimers, 1000);
setInterval(fetchOrders, 5000);
document.addEventListener('DOMContentLoaded', fetchOrders);