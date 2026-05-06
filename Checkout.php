<?php
session_start();
include "db_conn.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); 
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. Kunin ang lahat ng info kasama ang saved coordinates
$sql = "SELECT full_name, profile_pic, address_home, address_work, 
               lat_home, lng_home, lat_work, lng_work, 
               contact_number, discount_type 
        FROM create_acc WHERE id = '$user_id'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $user_data = mysqli_fetch_assoc($result);
    $fullname    = $user_data['full_name']; 
    $profile_pic = $user_data['profile_pic'] ?? ""; 
    $phone       = !empty($user_data['contact_number']) ? $user_data['contact_number'] : "No phone number set.";
    $home_addr   = !empty($user_data['address_home']) ? $user_data['address_home'] : "No home address set.";
    $work_addr   = !empty($user_data['address_work']) ? $user_data['address_work'] : "No work address set.";
    $discount_type = $user_data['discount_type'] ?? "None";
    
    // Default coordinates
    $lat_home = !empty($user_data['lat_home']) ? $user_data['lat_home'] : "14.5547";
    $lng_home = !empty($user_data['lng_home']) ? $user_data['lng_home'] : "121.0244";
    $lat_work = !empty($user_data['lat_work']) ? $user_data['lat_work'] : "14.5547";
    $lng_work = !empty($user_data['lng_work']) ? $user_data['lng_work'] : "121.0244";
} else {
    die("User not found.");
}

// 2. Approved Beneficiaries
$beneficiary_sql = "SELECT id, beneficiary_name, discount_type FROM user_discounts WHERE user_id = '$user_id' AND status = 'Approved'";
$beneficiary_result = mysqli_query($conn, $beneficiary_sql);
$beneficiaries = [];
while ($row = mysqli_fetch_assoc($beneficiary_result)) {
    $beneficiaries[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Kainan ni Ate Kabayan</title>
    <link rel="stylesheet" href="homepage.css">
    <link rel="stylesheet" href="Checkout.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Patrick+Hand&family=Poppins:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Leaflet.js Resources -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        .address-modal-overlay { 
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
            background: rgba(0,0,0,0.6); z-index: 9999; justify-content: center; align-items: center; 
        }
        .address-modal-overlay.active { display: flex; }
        .address-modal-content { 
            background-color: #FFF8E7; padding: 30px; border-radius: 0px; width: 95%; max-width: 500px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.2); text-align: left;
        }
        .modal-title { font-size: 1.8rem; font-weight: 800; margin-bottom: 20px; color: #000; }
        .address-list { display: flex; flex-direction: column; gap: 15px; }
        .modal-address-card { 
            background: #FEEBC8; border: 2px solid #000; border-radius: 15px; padding: 15px;
            display: flex; gap: 15px; cursor: pointer; transition: 0.3s; align-items: center;
        }
        .modal-address-card.active { background-color: #F4A42B !important; } 
        .modal-map-thumb { width: 80px; height: 80px; border-radius: 12px; overflow: hidden; border: 1px solid #000; flex-shrink: 0; position: relative; }
        .modal-map-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .address-info-box h3 { font-size: 1.1rem; font-weight: 800; margin: 0; color: #000; }
        .address-info-box p { font-size: 0.9rem; margin: 3px 0 0; line-height: 1.3; color: #000; }
        .address-textarea { width: 100%; border: 2px solid #000; border-radius: 10px; padding: 10px; font-family: inherit; font-size: 0.9rem; margin-top: 15px; background: #fff; resize: none; outline: none; }
        .modal-btns-row { display: flex; gap: 15px; margin-top: 15px; }
        .btn-modal { flex: 1; padding: 15px; border-radius: 15px; border: 2px solid #000; font-weight: 800; font-size: 1.1rem; cursor: pointer; transition: 0.2s; }
        .btn-cancel { background: white; color: #F4A42B; }
        .btn-save { background: #F4A42B; color: white; border: none; }
        
        #map { height: 250px; border-radius: 15px; border: 2px solid #000; margin-bottom: 15px; z-index: 1; }

        .pickup-info-card { background: #fff; border: 2px solid #000; border-radius: 15px; padding: 20px; margin-top: 10px; }
        .store-header { display: flex; align-items: center; gap: 12px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0; }
        .store-header i { color: #F4A42B; font-size: 1.8rem; }
        .pickup-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dashed #ddd; }
        .pickup-row:last-child { border-bottom: none; }
        .pickup-label { font-weight: 700; color: #555; font-size: 0.9rem; }
        .pickup-value { font-weight: 800; color: #000; text-align: right; }

        .hidden { display: none !important; }
        .pay-btn, .opt-btn { cursor: pointer; transition: 0.3s; }
        .pay-btn.active, .opt-btn.active { background-color: #F4A42B !important; color: white !important; border-color: #000 !important; }
        
        #discount-row { color: #27ae60; font-weight: 700; display: none; }
        .modal-address-card * { pointer-events: none; }
        .modal-address-card { pointer-events: auto !important; }

        .face-verify-card { border: 2px solid #000; border-radius: 15px; padding: 20px; background: #fff; margin-bottom: 20px; }
        .beneficiary-select { width: 100%; padding: 12px; border: 2px solid #000; border-radius: 10px; outline: none; margin-top: 10px; font-family: inherit; }

        .btn-pin-gps { 
            width: 100%; margin-top: 12px; padding: 12px; border: 2px solid #F4A42B; 
            border-radius: 12px; background: #fff; color: #F4A42B; font-weight: 800; 
            cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .btn-pin-gps.pinned { border-color: #2ecc71; color: #2ecc71; }

        /* Literal Submit Button Style */
        .btn-submit-location {
            width: 100%; margin-top: 10px; padding: 15px; border: none; 
            border-radius: 12px; background: #27ae60; color: #fff; 
            font-weight: 800; font-size: 1rem; cursor: pointer; transition: 0.3s;
            text-transform: uppercase;
        }
        .btn-submit-location:hover { background: #219150; }

        /* CSS PARA SA ADDRESS SUGGESTIONS SA CHECKOUT */
        .address-suggestions {
            background: #FFF3E0; border: 2px solid #F4A42B; border-radius: 10px;
            margin-top: 5px; max-height: 200px; overflow-y: auto; display: none;
            z-index: 10000; position: absolute; width: 100%; box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .suggestion-item {
            padding: 12px; cursor: pointer; font-size: 0.9rem; color: #000;
            font-weight: 600; border-bottom: 1px solid #FFE0B2; transition: 0.2s; text-align: left;
        }
        .suggestion-item:hover { background: #F4A42B; color: #fff; }
    </style>
</head>
<body>

<header>
    <div class="header-container">
        <a href="homepage.php" class="logo" style="text-decoration: none;">
            <div class="logo-circle"><img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772298452/logo_ate_kabayan_jtfqeg.jpg" alt="Logo"></div>
            <h2>KAINAN NI ATE KABAYAN</h2>
        </a>
        <nav class="desktop-nav">
            <a href="homepage.php">Home</a>
            <a href="menu.php">Menu</a>
            <a href="orders.php">Orders</a>
            <a href="ratings.php">Reviews</a>
            <a href="About Us.php">About Us</a>
            <a href="Contactus.php">Contact</a>
        </nav>
        <div class="header-actions">
            <a href="Profile.php" class="header-profile-desktop" style="text-decoration: none; display: flex; align-items: center; gap: 10px;">
                <div class="profile-img-small">
                    <?php if (!empty($profile_pic)): ?>
                        <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile">
                    <?php else: ?>
                        <i class="fa-solid fa-user"></i>
                    <?php endif; ?>
                </div>
                <span class="header-user-name" style="color: #fff; font-weight: bold;">HI, <?php echo strtoupper(explode(' ', trim($fullname))[0]); ?>!</span>
            </a>
            <a href="cart.php" class="cart-icon-btn">
                <i class="fa-solid fa-shopping-cart"></i>
                <span class="badge" id="cart-badge">0</span>
            </a>
            <div class="hamburger-menu" id="hamburger-btn"><i class="fa-solid fa-bars"></i></div>
        </div>
    </div>
</header>

<nav class="side-nav" id="side-nav">
    <div class="nav-profile">
        <div class="close-btn" id="close-btn"><i class="fa-solid fa-xmark"></i></div>
        <div class="profile-info">
            <div class="profile-img">
                <?php if (!empty($profile_pic)): ?>
                    <img src="<?php echo htmlspecialchars($profile_pic); ?>" style="width:100%; border-radius:50%; aspect-ratio: 1/1; object-fit: cover;">
                <?php else: ?>
                    <i class="fa-solid fa-user"></i>
                <?php endif; ?>
            </div>
            <div class="profile-text">
                <h3><?php echo htmlspecialchars($fullname); ?></h3>
                <a href="Profile.php">(View Profile)</a>
            </div>
        </div>
    </div>
    <div class="nav-links">
        <a href="homepage.php"><i class="fa-solid fa-house"></i> Home</a>
        <a href="menu.php"><i class="fa-solid fa-utensils"></i> Menu</a>
        <a href="orders.php"><i class="fa-solid fa-file-lines"></i> Orders</a>
        <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i> Cart</a>
        <a href="reviews.php"><i class="fa-solid fa-star"></i> Reviews</a>
        <a href="About Us.php"><i class="fa-solid fa-book-open"></i> About Us</a>
        <a href="Contactus.php"><i class="fa-solid fa-phone"></i> Contact Us</a>
        <a href="logout.php" class="logout"><i class="fa-solid fa-arrow-right-from-bracket"></i> Log Out</a>
    </div>
</nav>

<div class="overlay" id="overlay"></div>

<main class="checkout-wrapper">
    <div class="checkout-content-fit">
        <h1>Checkout</h1>

        <!-- Anti-Cheat Coordinates -->
        <input type="hidden" id="cust_lat" name="cust_lat" value="">
        <input type="hidden" id="cust_lng" name="cust_lng" value="">

        <div class="section-banner">Delivery Options</div>
        <div class="options-row">
            <button class="opt-btn active" id="btn-delivery"><i class="fa-solid fa-motorcycle"></i> Delivery</button>
            <button class="opt-btn" id="btn-pickup"><i class="fa-solid fa-location-dot"></i> Pick Up</button>
        </div>

        <div id="delivery-info-section">
            <div class="section-banner">Pin Delivery Location</div>
            <div id="map"></div>
            
            <div class="section-banner">Delivery Address</div>
            <div class="address-card" id="open-address-modal" style="cursor:pointer; border: 2px solid #000; border-radius: 15px; padding: 20px; display: flex; justify-content: space-between; align-items: center; background: #fff;">
                <div class="address-details">
                    <h3 id="display-label">Home <small>(Default)</small></h3>
                    <p id="display-text"><?php echo htmlspecialchars($home_addr); ?></p>
                </div>
                <div class="address-arrow"><i class="fa-solid fa-chevron-right"></i></div>
            </div>

            <button type="button" class="btn-pin-gps" id="btn-pin-location" onclick="pinCustomerGPS()">
                <i class="fa-solid fa-location-crosshairs"></i> Pin My Current Location
            </button>

            <!-- LITERAL NA SUBMIT BUTTON -->
            <button type="button" class="btn-submit-location" id="btn-save-pinned-location">Save Pinned Location</button>

            <small style="display:block; text-align:center; margin-top:5px; color:#777; font-size:0.75rem;">*Dragging the pin updates the address and delivery fee automatically.</small>

            <div class="section-banner">Delivery Instructions</div>
            <div class="instructions-box">
                <input type="text" id="delivery-note" placeholder="Note to rider - e.g landmark" style="width:100%; padding:15px; border:2px solid #000; border-radius:12px;">
            </div>
        </div>

        <div id="pickup-info-section" class="hidden">
            <div class="section-banner">Pick-up Details</div>
            <div class="pickup-info-card">
                <div class="store-header">
                    <i class="fa-solid fa-store"></i>
                    <div>
                        <strong style="font-size:1.1rem; color:#000;">Kainan ni Ate Kabayan</strong><br>
                        <small style="color:#555;">1785 Evangelista St., Bangkal, Makati City</small>
                    </div>
                </div>

                <div class="pickup-row">
                    <span class="pickup-label">Customer Name</span>
                    <span class="pickup-value"><?php echo htmlspecialchars($fullname); ?></span>
                </div>
                <div class="pickup-row">
                    <span class="pickup-label">Phone Number</span>
                    <span class="pickup-value"><?php echo htmlspecialchars($phone); ?></span>
                </div>
                <div class="pickup-row">
                    <span class="pickup-label">Ready in approx.</span>
                    <span class="pickup-value" style="color:#F4A42B;">15-20 Mins</span>
                </div>
            </div>
        </div>

        <div class="section-banner">Apply Discount (Senior/PWD)</div>
        <div class="face-verify-card">
            <label style="font-weight:700; font-size:0.9rem;">Sino ang gagamit ng discount?</label>
            <select id="beneficiary-select" class="beneficiary-select">
                <option value="">Regular Price</option>
                <?php foreach ($beneficiaries as $b): ?>
                    <option value="<?php echo $b['id']; ?>">
                        <?php echo htmlspecialchars($b['beneficiary_name']) . " (" . $b['discount_type'] . ")"; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small style="display:block; margin-top:8px; color:#555;">*Discount is applied automatically upon selection.</small>
        </div>

        <div class="section-banner">Payment Method</div>
        <div class="payment-row">
            <button class="pay-btn active" data-method="Cash"><i class="fa-solid fa-money-bill-wave"></i> Cash</button>
            <button class="pay-btn" data-method="Card"><i class="fa-solid fa-credit-card"></i> Card</button>
            <button class="pay-btn" data-method="E-Wallet"><i class="fa-solid fa-wallet"></i> E-Wallet</button>
        </div>

        <div class="section-banner">Order Summary</div>
        <div class="summary-card" style="border: 2px solid #000; border-radius: 15px; padding: 20px; background: #fff;">
            <div id="order-summary-list"></div>
            
            <div class="bill-details" style="margin-top:15px; border-top: 1px solid #eee; padding-top:10px;">
                <div class="pickup-row"><span>Subtotal</span><strong id="summary-subtotal">PHP 0.00</strong></div>
                <div class="pickup-row"><span>VAT (12%)</span><strong id="vat-display">PHP 0.00</strong></div>
                
                <div class="pickup-row" id="discount-row">
                    <span>Senior/PWD Discount</span>
                    <strong>- PHP <span id="discount-display">0.00</span></strong>
                </div>
                
                <div class="pickup-row" id="delivery-fee-row">
                    <span>Delivery Fee (Lalamove Rate)</span>
                    <strong id="delivery-fee-amount">PHP 0.00</strong>
                </div>

                <div class="pickup-row" id="cod-fee-row" style="display:none; color: #e67e22;">
                    <span>Cash on Delivery Fee</span>
                    <strong id="cod-fee-amount">PHP 50.00</strong>
                </div>
            </div>

            <div class="total-bar" style="display:flex; justify-content:space-between; margin-top:15px; border-top:2px solid #000; padding-top:15px;">
                <strong>Total</strong>
                <span style="font-size:1.4rem; color:#F4A42B; font-weight:900;">PHP <span id="summary-total">0.00</span></span>
            </div>
            
            <button class="btn-place-order" id="main-place-order-btn" onclick="placeOrder()" style="width:100%; margin-top:15px; padding: 18px; background: #F4A42B; color: #fff; border:none; border-radius: 15px; font-weight: 800; cursor:pointer;">Place Order</button>
        </div>
    </div>

    <!-- MODAL PARA SA ADDRESS -->
    <div class="address-modal-overlay" id="figmaModal">
        <div class="address-modal-content">
            <h2 class="modal-title">Delivery Address</h2>
            <div class="address-list">
                <div class="modal-address-card" id="modal-card-home" 
                     data-type="address_home" 
                     data-label="Home" 
                     data-addr="<?php echo htmlspecialchars($home_addr); ?>"
                     data-lat="<?php echo $lat_home; ?>"
                     data-lng="<?php echo $lng_home; ?>">
                    <div class="modal-map-thumb"><img src="https://maps.gstatic.com/tactile/pane/default_geocode-2x.png"></div>
                    <div class="address-info-box"><h3>Home</h3><p id="txt-home-m"><?php echo htmlspecialchars($home_addr); ?></p></div>
                </div>
                <div class="modal-address-card" id="modal-card-work" 
                     data-type="address_work" 
                     data-label="Work" 
                     data-addr="<?php echo htmlspecialchars($work_addr); ?>"
                     data-lat="<?php echo $lat_work; ?>"
                     data-lng="<?php echo $lng_work; ?>">
                    <div class="modal-map-thumb"><img src="https://maps.gstatic.com/tactile/pane/default_geocode-2x.png"></div>
                    <div class="address-info-box"><h3>Work</h3><p id="txt-work-m"><?php echo htmlspecialchars($work_addr); ?></p></div>
                </div>
            </div>
            
            <!-- TYPEBOX WITH SUGGESTIONS CONTAINER -->
            <div style="margin-top:15px; position: relative;">
                <label id="edit-label" style="font-size:0.8rem; font-weight:700;">Manual Address Edit (Required):</label>
                <textarea id="edit-textarea" class="address-textarea" rows="2" required placeholder="Type your detailed address here..."></textarea>
                <div id="address_suggestions" class="address-suggestions"></div>
            </div>

            <div class="modal-btns-row">
                <button class="btn-modal btn-cancel" id="cancel-modal">Cancel</button>
                <button class="btn-modal btn-save" id="save-address-btn">Save & Select</button>
            </div>
        </div>
    </div>
</main>

<!-- CUSTOM ALERT MODAL -->
<div class="address-modal-overlay" id="customAlertOverlay">
    <div class="address-modal-content" style="border-radius: 15px; text-align: center; max-width: 400px; padding: 25px;">
        <p id="customAlertMessage" style="font-weight: 600; font-size: 1.1rem; color: #333; margin-bottom: 20px;"></p>
        <div class="modal-btns-row" id="customAlertBtns" style="justify-content: center;"></div>
    </div>
</div>

<script>
    // --- CUSTOM ALERT LOGIC ---
    function showCustomAlert(message) {
        const overlay = document.getElementById('customAlertOverlay');
        document.getElementById('customAlertMessage').innerText = message;
        const btnsEl = document.getElementById('customAlertBtns');
        btnsEl.innerHTML = '';
        
        const btnOk = document.createElement('button');
        btnOk.className = 'btn-modal btn-save';
        btnOk.innerText = 'OK';
        btnOk.onclick = () => { overlay.classList.remove('active'); };
        btnsEl.appendChild(btnOk);
        
        overlay.classList.add('active');
    }

    window.currentUserId = "<?php echo $user_id; ?>";
    const cartKey = 'myCart'; 
    localStorage.setItem('userDiscountType', "<?php echo $discount_type; ?>");

    let selectedType = localStorage.getItem('lastSelectedType') || "address_home";
    const displayLabel = document.getElementById('display-label');
    const displayText = document.getElementById('display-text');
    const textarea = document.getElementById('edit-textarea');
    const figmaModal = document.getElementById('figmaModal');

    // Bridge Logic: I-initialize ang Map base sa huling saved coordinates sa DB
    let initialLat = selectedType === 'address_work' ? "<?php echo $lat_work; ?>" : "<?php echo $lat_home; ?>";
    let initialLng = selectedType === 'address_work' ? "<?php echo $lng_work; ?>" : "<?php echo $lng_home; ?>";

    // NCR Bounds for Restricting Map
    var ncrBounds = [[14.33, 120.89], [14.77, 121.14]];

    var map = L.map('map', {
        maxBounds: ncrBounds,
        maxBoundsViscosity: 1.0
    }).setView([initialLat, initialLng], 16);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    var marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);
    
    document.getElementById('cust_lat').value = initialLat;
    document.getElementById('cust_lng').value = initialLng;

    const runUpdate = () => {
        if(typeof window.triggerRender === 'function') {
            window.triggerRender();
        }
    };

    // --- AUTO-SYNC PIN TO ADDRESS TEXT LOGIC ---
    function syncPinWithAddress(addressText) {
        if (!addressText || addressText.trim() === "" || addressText.includes("No home address") || addressText.includes("No work address")) return;
        fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(addressText)}&email=atekabayan@example.com`)
            .then(r => r.json()).then(data => {
                if (data.length > 0 && marker && map) {
                    const lat = parseFloat(data[0].lat);
                    const lng = parseFloat(data[0].lon);
                    marker.setLatLng([lat, lng]); 
                    map.setView([lat, lng], 17);
                    document.getElementById('cust_lat').value = lat;
                    document.getElementById('cust_lng').value = lng;
                    runUpdate();
                }
            }).catch(e => console.log('Sync error:', e));
    }

    function fetchAddress(lat, lng) {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&email=atekabayan@example.com`)
            .then(res => res.json())
            .then(data => {
                if (data.display_name) {
                    const addrStr = data.display_name.toLowerCase();
                    const ncrRegex = /metro manila|ncr|national capital region|makati|manila|taguig|quezon city|pasig|mandaluyong|pasay|san juan|pateros|valenzuela|caloocan|malabon|navotas|marikina|muntinlupa|las piñas|parañaque/i;
                    
                    if (!ncrRegex.test(addrStr)) {
                        showCustomAlert("Pasensya na, Kabayan! Hanggang Metro Manila lang po ang sakop ng aming serbisyo.");
                        marker.setLatLng([initialLat, initialLng]);
                        map.setView([initialLat, initialLng], 16);
                        return;
                    }

                    displayText.innerText = data.display_name;
                    textarea.value = data.display_name;
                    document.getElementById('cust_lat').value = lat;
                    document.getElementById('cust_lng').value = lng;
                    runUpdate(); 
                }
            });
    }

    marker.on('dragend', function() {
        var pos = marker.getLatLng();
        if (pos.lat < 14.33 || pos.lat > 14.77 || pos.lng < 120.89 || pos.lng > 121.14) {
            showCustomAlert("Pasensya na, Kabayan! Hanggang Metro Manila lang po ang sakop namin.");
            marker.setLatLng([initialLat, initialLng]);
            map.setView([initialLat, initialLng], 16);
            return;
        }
        fetchAddress(pos.lat, pos.lng);
    });

    map.on('click', function(e) {
        if (e.latlng.lat < 14.33 || e.latlng.lat > 14.77 || e.latlng.lng < 120.89 || e.latlng.lng > 121.14) {
            showCustomAlert("Pasensya na, Kabayan! Hanggang Metro Manila lang po ang sakop namin.");
            return;
        }
        marker.setLatLng(e.latlng);
        fetchAddress(e.latlng.lat, e.latlng.lng);
    });

    // --- ADDRESS SUGGESTION / AUTOCOMPLETE SA MODAL ---
    let searchTimer;
    textarea.addEventListener('input', function() {
        clearTimeout(searchTimer);
        const addressText = this.value;
        const suggBox = document.getElementById('address_suggestions');
        
        if (!addressText || addressText.trim().length < 4) { 
            if(suggBox) suggBox.style.display = 'none'; 
            return; 
        }

        searchTimer = setTimeout(() => {
            fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=10&q=${encodeURIComponent(addressText + " Metro Manila")}&countrycodes=ph&viewbox=120.89,14.77,121.14,14.33&bounded=1&addressdetails=1&email=atekabayan@example.com`)
                .then(r => r.json()).then(data => {
                    let filteredData = data.filter(item => {
                        let addrStr = item.display_name.toLowerCase();
                        return addrStr.includes("metro manila") || addrStr.includes("national capital region") || addrStr.includes("ncr");
                    });
                    
                    filteredData = filteredData.slice(0, 5);

                    if (filteredData.length > 0 && suggBox) {
                        suggBox.innerHTML = ''; 
                        suggBox.style.display = 'block';
                        filteredData.forEach(item => {
                            let div = document.createElement('div');
                            div.className = 'suggestion-item'; 
                            div.innerText = item.display_name;
                            div.onclick = () => {
                                textarea.value = item.display_name;
                                const lat = parseFloat(item.lat);
                                const lng = parseFloat(item.lon);
                                if(marker && map) {
                                    marker.setLatLng([lat, lng]); 
                                    map.setView([lat, lng], 17);
                                }
                                document.getElementById('cust_lat').value = lat;
                                document.getElementById('cust_lng').value = lng;
                                displayText.innerText = item.display_name;
                                suggBox.style.display = 'none';
                                runUpdate();
                            };
                            suggBox.appendChild(div);
                        });
                    } else {
                        if(suggBox) suggBox.style.display = 'none';
                    }
                }).catch(() => { console.log("Nominatim is busy..."); });
        }, 1000); 
    });

    // Hide suggestions pag-click sa labas
    document.addEventListener('click', (e) => {
        if (!e.target.classList.contains('address-textarea')) {
            const suggBox = document.getElementById('address_suggestions');
            if(suggBox) suggBox.style.display = 'none';
        }
    });

    function initDisplay() {
        const homeAddr = `<?php echo addslashes($home_addr); ?>`;
        const workAddr = `<?php echo addslashes($work_addr); ?>`;
        document.querySelectorAll('.modal-address-card').forEach(c => c.classList.remove('active'));

        if (selectedType === 'address_work') {
            displayLabel.innerHTML = "Work";
            displayText.innerText = workAddr;
            document.getElementById('modal-card-work').classList.add('active');
            textarea.value = workAddr;
            syncPinWithAddress(workAddr); // TRIGGER AUTO-SYNC
        } else {
            displayLabel.innerHTML = "Home <small>(Default)</small>";
            displayText.innerText = homeAddr;
            document.getElementById('modal-card-home').classList.add('active');
            textarea.value = homeAddr;
            syncPinWithAddress(homeAddr); // TRIGGER AUTO-SYNC
        }
    }
    initDisplay();

    function pinCustomerGPS() {
        const btn = document.getElementById('btn-pin-location');
        if (navigator.geolocation) {
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Finding you...';
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                if (lat < 14.33 || lat > 14.77 || lng < 120.89 || lng > 121.14) {
                    showCustomAlert("Nasa labas ka ng Metro Manila, Kabayan! Hindi namin ma-pin ang current location mo.");
                    btn.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i> Pin My Current Location';
                    return;
                }

                document.getElementById('cust_lat').value = lat;
                document.getElementById('cust_lng').value = lng;
                map.setView([lat, lng], 18);
                marker.setLatLng([lat, lng]);
                fetchAddress(lat, lng); 
                btn.innerHTML = '<i class="fa-solid fa-circle-check"></i> Location Pinned!';
                btn.classList.add('pinned');
            }, function(error) {
                btn.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i> Use Current GPS Location';
            });
        }
    }

    // --- FUNCTION PARA SA BAGONG SAVE BUTTON (SILENT SAVE) ---
    document.getElementById('btn-save-pinned-location').addEventListener('click', function() {
        const btn = this;
        const originalText = btn.innerText;
        const currentAddr = document.getElementById('display-text').innerText;
        const lat = document.getElementById('cust_lat').value;
        const lng = document.getElementById('cust_lng').value;

        if(!lat || !lng) return;

        // Metro Manila Validation
        const ncrRegex = /metro manila|ncr|national capital region|makati|manila|taguig|quezon city|pasig|mandaluyong|pasay|san juan|pateros|valenzuela|caloocan|malabon|navotas|marikina|muntinlupa|las piñas|parañaque/i;
        if (!ncrRegex.test(currentAddr.toLowerCase())) {
            showCustomAlert('Kabayan, ang Address ay dapat nasa loob ng Metro Manila.');
            return;
        }

        btn.innerText = "SAVING..."; 

        fetch('update_address_ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `type=${selectedType}&address=${encodeURIComponent(currentAddr)}&lat=${lat}&lng=${lng}`
        })
        .then(res => res.text())
        .then(data => {
            if(data.trim() === "success") {
                btn.innerText = "SAVED!"; 
                initialLat = lat; 
                initialLng = lng;
                setTimeout(() => { btn.innerText = originalText; }, 2000);
            } else {
                btn.innerText = "ERROR!";
                setTimeout(() => { btn.innerText = originalText; }, 2000);
            }
        });
    });

    document.getElementById('open-address-modal').addEventListener('click', () => figmaModal.classList.add('active'));
    document.getElementById('cancel-modal').addEventListener('click', () => figmaModal.classList.remove('active'));

    document.querySelectorAll('.modal-address-card').forEach(card => {
        card.addEventListener('click', function() {
            document.querySelectorAll('.modal-address-card').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            selectedType = this.getAttribute('data-type');
            textarea.value = this.getAttribute('data-addr');
            
            // Sync Map Pin automatically when switching address cards
            syncPinWithAddress(textarea.value);
            runUpdate();
        });
    });

    document.getElementById('save-address-btn').addEventListener('click', function() {
        const newAddr = textarea.value;
        const lat = document.getElementById('cust_lat').value;
        const lng = document.getElementById('cust_lng').value;

        if(!newAddr.trim()) return;

        const ncrRegex = /metro manila|ncr|national capital region|makati|manila|taguig|quezon city|pasig|mandaluyong|pasay|san juan|pateros|valenzuela|caloocan|malabon|navotas|marikina|muntinlupa|las piñas|parañaque/i;
        if (!ncrRegex.test(newAddr.toLowerCase())) {
            showCustomAlert('Kabayan, ang Address ay dapat nasa loob ng Metro Manila.');
            return;
        }

        localStorage.setItem('lastSelectedType', selectedType);
        
        fetch('update_address_ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `type=${selectedType}&address=${encodeURIComponent(newAddr)}&lat=${lat}&lng=${lng}`
        }).then(() => {
            displayText.innerText = newAddr;
            figmaModal.classList.remove('active');
            initialLat = lat; 
            initialLng = lng;
            runUpdate();
            location.reload(); 
        });
    });

    document.getElementById('btn-delivery').addEventListener('click', () => {
        document.getElementById('btn-delivery').classList.add('active');
        document.getElementById('btn-pickup').classList.remove('active');
        document.getElementById('delivery-info-section').classList.remove('hidden');
        document.getElementById('pickup-info-section').classList.add('hidden');
        runUpdate(); 
    });

    document.getElementById('btn-pickup').addEventListener('click', () => {
        document.getElementById('btn-pickup').classList.add('active');
        document.getElementById('btn-delivery').classList.remove('active');
        document.getElementById('delivery-info-section').classList.add('hidden');
        document.getElementById('pickup-info-section').classList.remove('hidden');
        runUpdate(); 
    });

    document.getElementById('hamburger-btn').addEventListener('click', () => {
        document.getElementById('side-nav').classList.add('active');
        document.getElementById('overlay').classList.add('active');
    });
    document.getElementById('close-btn').addEventListener('click', () => {
        document.getElementById('side-nav').classList.remove('active');
        document.getElementById('overlay').classList.remove('active');
    });
    document.getElementById('overlay').addEventListener('click', () => {
        document.getElementById('side-nav').classList.remove('active');
        document.getElementById('overlay').classList.remove('active');
    });

    fetch('get_cart_count.php').then(r=>r.json()).then(d=>{
        const b=document.getElementById('cart-badge');
        if(b){b.innerText=d.total_qty||0;b.style.display=(d.total_qty>0)?'flex':'none';}
    }).catch(()=>{});
</script>

<script src="Checkout.js"></script>
</body>
</html>