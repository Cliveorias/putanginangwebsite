<?php
session_start();
include "db_conn.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$order_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Fetch user profile data para sa sidebar
$user_sql = "SELECT full_name, profile_pic FROM create_acc WHERE id = '$user_id'";
$user_res = mysqli_query($conn, $user_sql);
$user_data = mysqli_fetch_assoc($user_res);

// AJAX CHECK: Para sa automatic update nang walang refresh
if (isset($_GET['check_status'])) {
    $sql = "SELECT tracking_link FROM orders WHERE id = '$order_id' AND user_id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    echo json_encode(['tracking_link' => $row['tracking_link'] ?? ""]);
    exit();
}

// Initial Fetch
$sql = "SELECT tracking_link, status FROM orders WHERE id = '$order_id' AND user_id = '$user_id'";
$result = mysqli_query($conn, $sql);
$order = mysqli_fetch_assoc($result);
$tracking_url = !empty($order['tracking_link']) ? $order['tracking_link'] : "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Your Order - Ate Kabayan</title>
    <link rel="stylesheet" href="homepage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-orange: #FFBD59; 
            --highlight-orange: #F4A42B; 
            --white: #ffffff;
            --cream: #FFF8E7;
            --order-now-red: #C92C1C;
            --text-dark: #333;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: var(--cream); color: var(--text-dark); overflow-x: hidden; }

        header {
            background-color: var(--primary-orange); 
            padding: 15px 0; 
            position: sticky;
            top: 0;
            z-index: 9999;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo { display: flex; align-items: center; gap: 12px; color: var(--white); text-decoration: none; }
        .logo-circle { width: 45px; height: 45px; background: white; border-radius: 50%; display: flex; justify-content: center; align-items: center; overflow: hidden; border: 2px solid white; }
        .logo-circle img { width: 100%; height: 100%; object-fit: cover; }
        .logo h2 { font-family: 'Fredoka One', cursive; font-size: 1.1rem; font-weight: 800; text-transform: uppercase; color: var(--white); line-height: 1; }

        .desktop-nav { display: flex; gap: 15px; }
        .desktop-nav a { text-decoration: none; color: var(--white); font-weight: 700; font-size: 0.85rem; text-transform: uppercase; padding: 8px 12px; border-radius: 8px; transition: 0.3s; }
        .desktop-nav a:hover { background: rgba(255, 255, 255, 0.2); color: var(--order-now-red); }

        .header-actions { display: flex; align-items: center; gap: 15px; }
        .header-profile-desktop { display: flex; align-items: center; gap: 8px; text-decoration: none; color: var(--white); background: rgba(255, 255, 255, 0.2); padding: 4px 12px 4px 4px; border-radius: 50px; border: 1px solid rgba(255, 255, 255, 0.3); }
        .profile-img-small { width: 32px; height: 32px; border-radius: 50%; overflow: hidden; background: #fff; display: flex; justify-content: center; align-items: center; }
        .profile-img-small img { width: 100%; height: 100%; object-fit: cover; }

        .cart-icon-btn { position: relative; background: var(--white); width: 40px; height: 40px; border-radius: 10px; display: flex; justify-content: center; align-items: center; color: var(--primary-orange); font-size: 1.2rem; text-decoration: none; }
        .badge { position: absolute; top: -5px; right: -5px; background: #ff4757; color: var(--white); font-size: 0.7rem; font-weight: bold; padding: 2px 6px; border-radius: 50%; border: 2px solid var(--white); }

        .hamburger-menu { cursor: pointer; font-size: 1.8rem; color: var(--white); display: none; align-items: center; }
        .header-user-name { font-size: 0.8rem; font-weight: 800; text-transform: uppercase; }

        @media (min-width: 1025px) { .hamburger-menu { display: none !important; } }
        @media (max-width: 1024px) {
            .desktop-nav, .header-profile-desktop, .cart-icon-btn { display: none !important; }
            .hamburger-menu { display: flex; }
            .header-container { padding: 0 15px; }
        }

        .side-nav { position: fixed; top: 0; right: -320px; width: 300px; height: 100vh; background-color: #FFF3E0; z-index: 10000; transition: 0.4s; box-shadow: -5px 0 15px rgba(0,0,0,0.1); display: flex; flex-direction: column; }
        .side-nav.active { right: 0; }
        .nav-profile { background-color: #F49D35; padding: 30px 20px; position: relative; color: white; }
        .profile-info { display: flex; align-items: center; gap: 15px; }
        .profile-img { width: 60px; height: 60px; background: #ccc; border-radius: 50%; display: flex; justify-content: center; align-items: center; overflow: hidden; }
        .profile-img img { width: 100%; height: 100%; object-fit: cover; }
        .profile-text h3 { font-size: 1.1rem; margin: 0; }
        .profile-text a { font-size: 0.8rem; color: rgba(255,255,255,0.8); text-decoration: none; }
        .close-btn { position: absolute; top: 10px; right: 15px; font-size: 1.5rem; cursor: pointer; color: white; }
        .nav-links { padding: 15px; display: flex; flex-direction: column; gap: 10px; }
        .nav-links a { display: flex; align-items: center; gap: 15px; padding: 12px 20px; background-color: #F49D35; color: white; text-decoration: none; border-radius: 10px; font-weight: 700; }
        .nav-links a:hover { background-color: #d8892d; transform: translateX(-5px); }
        .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9998; display: none; }
        .overlay.active { display: block; }

        main { padding: 40px 5%; display: flex; flex-direction: column; align-items: center; text-align: center; }
        h1 { font-weight: 900; color: var(--highlight-orange); font-size: 2rem; margin-bottom: 10px; }
        .map-wrapper { width: 100%; max-width: 600px; height: 450px; background: #eee; border-radius: 30px; overflow: hidden; border: 5px solid white; box-shadow: 0 30px 60px rgba(0,0,0,0.1); position: relative; margin-bottom: 25px; transform: rotateX(5deg); transition: 0.5s; }
        .map-wrapper:hover { transform: rotateX(0deg); }
        iframe { width: 100%; height: 100%; border: none; }
        .live-tag { position: absolute; top: 20px; left: 20px; background: #ff4757; color: white; padding: 5px 15px; border-radius: 20px; font-weight: 800; font-size: 0.7rem; z-index: 10; display: none; animation: blink 1.5s infinite; }
        @keyframes blink { 50% { opacity: 0.5; } }

        .btn-locate { background: var(--highlight-orange); color: white; padding: 15px 35px; border-radius: 50px; text-decoration: none; font-weight: 800; display: inline-block; transition: 0.3s; box-shadow: 0 10px 20px rgba(244, 164, 43, 0.2); border: none; cursor: pointer; margin-bottom: 15px; }

        /* BUTTON RECEIVED */
        .btn-received {
            background: #27ae60; color: white; padding: 18px 50px; border-radius: 50px;
            text-decoration: none; font-weight: 900; font-size: 1.2rem;
            display: inline-block; transition: 0.3s; border: none; cursor: pointer;
            box-shadow: 0 10px 20px rgba(39, 174, 96, 0.3); margin-top: 10px;
        }
        .btn-received:hover { transform: scale(1.05); background: #2ecc71; box-shadow: 0 15px 30px rgba(39, 174, 96, 0.4); }

        /* CUSTOM MODAL STYLES[cite: 7] */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.6); backdrop-filter: blur(8px);
            display: none; justify-content: center; align-items: center; z-index: 20000;
        }
        .modal-card {
            background: white; padding: 35px; border-radius: 30px; width: 90%; max-width: 420px;
            text-align: center; box-shadow: 0 25px 50px rgba(0,0,0,0.2);
            animation: popIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        @keyframes popIn { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .modal-card i { font-size: 4rem; color: #27ae60; margin-bottom: 20px; }
        .modal-card h2 { font-family: 'Fredoka One', cursive; margin-bottom: 10px; color: #333; }
        .modal-card p { color: #666; margin-bottom: 30px; font-weight: 500; }
        .modal-btns { display: flex; gap: 15px; }
        .modal-btn { flex: 1; padding: 15px; border: none; border-radius: 15px; font-weight: 800; cursor: pointer; text-transform: uppercase; transition: 0.2s; }
        .btn-cancel { background: #eee; color: #777; }
        .btn-confirm { background: #27ae60; color: white; }
        .btn-confirm:hover { background: #219150; }

        footer { background: var(--primary-orange); color: white; padding: 40px 5%; border-radius: 40px 40px 0 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; margin-top: 50px; }
    </style>
</head>
<body>

<div class="overlay" id="overlay"></div>

<!-- CUSTOM MODAL HTML[cite: 7] -->
<div class="modal-overlay" id="confirmModal">
    <div class="modal-card">
        <i class="fa-solid fa-circle-check"></i>
        <h2>Order Received?</h2>
        <p>Natanggap mo na ba ang masarap na luto ni Ate Kabayan? I-confirm na para maka-rate!</p>
        <div class="modal-btns">
            <button class="modal-btn btn-cancel" onclick="closeModal()">Hindi pa</button>
            <button class="modal-btn btn-confirm" onclick="confirmReceived()">Oo, nakuha na!</button>
        </div>
    </div>
</div>

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
                    <?php if (!empty($user_data['profile_pic'])): ?>
                        <img src="<?php echo htmlspecialchars($user_data['profile_pic']); ?>" alt="Profile">
                    <?php else: ?>
                        <i class="fa-solid fa-user"></i>
                    <?php endif; ?>
                </div>
                <span class="header-user-name" style="color: #fff; font-weight: bold;">HI, <?php echo strtoupper(explode(' ', trim($user_data['full_name']))[0]); ?>!</span>
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
                <?php if (!empty($user_data['profile_pic'])): ?>
                    <img src="<?php echo htmlspecialchars($user_data['profile_pic']); ?>" style="width:100%; border-radius:50%; aspect-ratio: 1/1; object-fit: cover;">
                <?php else: ?>
                    <i class="fa-solid fa-user"></i>
                <?php endif; ?>
            </div>
            <div class="profile-text">
                <h3><?php echo htmlspecialchars($user_data['full_name']); ?></h3>
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

<main>
    <h1>Otw na, Kabayan!</h1>
    <p style="color:#666; font-weight:600; margin-bottom:20px;" id="status-text"><?php echo $tracking_url ? "Your rider is on the way!" : "Hinahanda na ang delivery tracking..."; ?></p>

    <div class="map-wrapper">
        <div class="live-tag" id="live-tag" style="<?php echo $tracking_url ? 'display:block;' : ''; ?>">● LIVE</div>
        <div id="dynamic-content" style="height:100%;">
            <?php if($tracking_url): ?>
                <iframe src="<?php echo $tracking_url; ?>" allow="geolocation"></iframe>
            <?php else: ?>
                <div style="height:100%; display:flex; flex-direction:column; justify-content:center; align-items:center; background:#f9f9f9;">
                    <i class="fa-solid fa-truck-fast" style="font-size:4rem; color:var(--primary-orange); margin-bottom:15px; animation: bounce 2s infinite;"></i>
                    <p style="color:#aaa; font-weight:600;">Waiting for rider info...</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <a href="<?php echo $tracking_url; ?>" id="full-map-btn" target="_blank" class="btn-locate" style="<?php echo $tracking_url ? '' : 'opacity:0.5; pointer-events:none;'; ?>">
        <i class="fa-solid fa-location-arrow"></i> OPEN FULL MAP
    </a>

    <!-- BINAGONG BUTTON: Tatawag na sa Modal[cite: 7] -->
    <button type="button" onclick="openModal()" class="btn-received">
        <i class="fa-solid fa-check-double"></i> ORDER RECEIVED
    </button>

    <div style="margin-top:20px; font-weight:800; color:var(--highlight-orange); border:2px solid; padding:10px 25px; border-radius:50px; background:white;">Estimated Time: 5-10 mins</div>
</main>

<footer>
    <div><h4 style="margin-bottom:15px; font-weight:900;">ATE KABAYAN</h4><p style="font-size:0.8rem; opacity:0.9;">1785 Evangelista St., Bangkal, Makati City</p></div>
    <div><h4 style="margin-bottom:15px; font-weight:900;">SITEMAP</h4><p style="font-size:0.8rem; line-height:2;">Home<br>Menu<br>Cart</p></div>
</footer>

<script>
    // --- BURGER MENU LOGIC ---
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

    // --- CART BADGE COUNT ---
    fetch('get_cart_count.php').then(r=>r.json()).then(d=>{
        const b=document.getElementById('cart-badge');
        if(b){b.innerText=d.total_qty||0;b.style.display=(d.total_qty>0)?'flex':'none';}
    }).catch(()=>{});

    // MODAL FUNCTIONS[cite: 7]
    function openModal() {
        document.getElementById('confirmModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('confirmModal').style.display = 'none';
    }

    function confirmReceived() {
        const orderId = "<?php echo $order_id; ?>";
        const formData = new FormData();
        formData.append('id', orderId);
        formData.append('action', 'complete_direct'); // Action sa update_kitchen_status.php[cite: 5]

        fetch('update_kitchen_status.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(data => {
            if (data.trim() === "success") {
                // Pag-success, diretso sa rating page[cite: 6]
                window.location.href = `rate.php?id=${orderId}`;
            } else {
                alert("May error sa system: " + data);
            }
        })
        .catch(err => console.error("Error:", err));
    }

    const orderId = "<?php echo $order_id; ?>";
    let hasLink = <?php echo $tracking_url ? 'true' : 'false'; ?>;

    setInterval(() => {
        if (!hasLink) {
            fetch(`otw.php?id=${orderId}&check_status=1`)
                .then(res => res.json())
                .then(data => {
                    if (data.tracking_link && data.tracking_link !== "") {
                        document.getElementById('dynamic-content').innerHTML = `<iframe src="${data.tracking_link}" allow="geolocation"></iframe>`;
                        document.getElementById('live-tag').style.display = 'block';
                        document.getElementById('full-map-btn').href = data.tracking_link;
                        document.getElementById('full-map-btn').style.opacity = '1';
                        document.getElementById('full-map-btn').style.pointerEvents = 'auto';
                        document.getElementById('status-text').innerText = "Your rider is on the way!";
                        hasLink = true;
                    }
                });
        }
    }, 5000);
</script>

</body>
</html>