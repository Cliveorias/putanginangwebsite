<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "db_conn.php"; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['id'] ?? 0;

// 1. Kunin ang order details - Isinama ang order_type para sa redirection logic
$sql = "SELECT id, status, prep_time, prep_start_time, estimated_time, order_type FROM orders WHERE id = '$order_id' AND user_id = '$user_id'";
$result = mysqli_query($conn, $sql);
$order = mysqli_fetch_assoc($result);

// 2. Kunin ang user profile data para sa sidebar
$user_sql = "SELECT full_name, profile_pic FROM create_acc WHERE id = '$user_id'";
$user_res = mysqli_query($conn, $user_sql);
$user_data = mysqli_fetch_assoc($user_res);

if (!$order) {
    echo "<div style='text-align:center; padding:50px; font-family:sans-serif;'>
            <h2>Order Not Found</h2>
            <p>Pasensya na Kabayan, hindi namin mahanap ang Order #$order_id.</p>
            <a href='homepage.php'>Balik sa Home</a>
          </div>";
    exit();
}

// 3. PHP-side Redirect: Kung Completed na agad ang Pick Up order pag-load
// Binago ang link patungong rate.php
if (strtolower($order['status']) == 'completed' && strtolower($order['order_type']) == 'pick up') {
    header("Location: rate.php?id=" . $order_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preparing Order - Ate Kabayan</title>
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
        body { background-color: var(--cream); color: var(--text-dark); min-height: 100vh; display: flex; flex-direction: column; overflow-x: hidden; }

        header {
            background-color: rgba(255, 189, 89, 0.85); 
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 15px 0; 
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
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
        .logo-circle img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
        .logo h2 { font-family: 'Fredoka One', cursive; font-size: 1.1rem; font-weight: 800; text-transform: uppercase; margin: 0; text-shadow: 1px 1px 2px rgba(0,0,0,0.2); letter-spacing: 0.5px; color: var(--white); line-height: 1; }

        .desktop-nav { display: flex; gap: 15px; }
        .desktop-nav a { text-decoration: none; color: var(--white); font-weight: 700; font-size: 0.85rem; text-transform: uppercase; transition: all 0.3s ease; padding: 8px 12px; border-radius: 8px; }
        .desktop-nav a:hover { color: var(--order-now-red); background: rgba(255, 255, 255, 0.2); text-shadow: 0 0 10px rgba(255, 255, 255, 0.6), 0 0 20px var(--order-now-red); transform: translateY(-2px); }

        .header-actions { display: flex; align-items: center; gap: 15px; }
        .header-profile-desktop { display: flex; align-items: center; gap: 8px; text-decoration: none; color: var(--white); background: rgba(255, 255, 255, 0.2); padding: 4px 12px 4px 4px; border-radius: 50px; border: 1px solid rgba(255, 255, 255, 0.3); transition: 0.3s; }
        .profile-img-small { width: 32px; height: 32px; border-radius: 50%; overflow: hidden; background: #fff; display: flex; justify-content: center; align-items: center; }
        .profile-img-small img { width: 100%; height: 100%; object-fit: cover; }
        .header-user-name { font-size: 0.8rem; font-weight: 800; text-transform: uppercase; }

        .cart-icon-btn { position: relative; background: var(--white); width: 40px; height: 40px; border-radius: 10px; display: flex; justify-content: center; align-items: center; color: var(--primary-orange); font-size: 1.2rem; text-decoration: none; transition: transform 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .badge { position: absolute; top: -5px; right: -5px; background: #ff4757; color: var(--white); font-size: 0.7rem; font-weight: bold; padding: 2px 6px; border-radius: 50%; border: 2px solid var(--white); }

        .hamburger-menu { cursor: pointer; font-size: 1.8rem; color: var(--white); display: none; align-items: center; }

        @media (max-width: 1024px) {
            .desktop-nav, .header-profile-desktop, .cart-icon-btn { display: none !important; }
            .hamburger-menu { display: flex; }
        }

        .side-nav {
            position: fixed; top: 0; right: -320px; width: 300px; height: 100vh;
            background-color: #FFF3E0; z-index: 2000; transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: -5px 0 15px rgba(0,0,0,0.1); display: flex; flex-direction: column;
        }
        .side-nav.active { right: 0; }

        .nav-profile { background-color: #F49D35; padding: 30px 20px; position: relative; color: white; }
        .profile-info { display: flex; align-items: center; gap: 15px; }
        .profile-img { width: 60px; height: 60px; background: #ccc; border-radius: 50%; display: flex; justify-content: center; align-items: center; overflow: hidden; }
        .profile-img img { width: 100%; height: 100%; object-fit: cover; }
        .profile-text h3 { font-size: 1.1rem; margin: 0; }
        .profile-text a { font-size: 0.8rem; color: rgba(255,255,255,0.8); text-decoration: none; }
        .close-btn { position: absolute; top: 10px; right: 15px; font-size: 1.5rem; cursor: pointer; color: white; }

        .nav-links { padding: 15px; display: flex; flex-direction: column; gap: 10px; }
        .nav-links a { display: flex; align-items: center; gap: 15px; padding: 12px 20px; background-color: #F49D35; color: white; text-decoration: none; border-radius: 10px; font-weight: 700; font-size: 1rem; transition: 0.3s; }
        .nav-links a:hover { background-color: #d8892d; transform: translateX(-5px); }

        .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1500; display: none; }
        .overlay.active { display: block; }

        main { flex: 1; display: flex; justify-content: center; align-items: center; padding: 40px 20px; }
        .preparing-card { background: white; width: 100%; max-width: 500px; padding: 40px 30px; border-radius: 30px; text-align: center; border: 2.5px solid #000; }
        .cooking-visual { width: 180px; height: 180px; background: #FFD580; border-radius: 50%; margin: 0 auto 30px; display: flex; justify-content: center; align-items: center; border: 5px solid var(--primary-orange); }
        .cooking-visual i { font-size: 70px; color: #E67E22; animation: panSizzle 1.5s infinite ease-in-out; }
        @keyframes panSizzle { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-15px); } }
        h1 { font-weight: 800; font-size: 1.7rem; color: var(--highlight-orange); margin-bottom: 10px; }
        .eta-box { background: #FFF3E0; padding: 20px; border-radius: 20px; border: 2px dashed var(--highlight-orange); margin-top: 20px; }
        .eta-time { font-size: 2.5rem; font-weight: 900; color: var(--highlight-orange); }

        footer { background: var(--primary-orange); padding: 40px 10%; border-top-left-radius: 40px; border-top-right-radius: 40px; margin-top: auto; }
        .footer-grid { display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 30px; color: white; }
        .footer-logo img { width: 100px; border-radius: 50%; border: 3px solid white; margin-bottom: 10px; }
        .footer-col a { display: block; color: white; text-decoration: none; font-size: 0.85rem; margin-bottom: 10px; }
    </style>
</head>
<body>

<header>
    <div class="header-container">
        <a href="homepage.php" class="logo" style="text-decoration: none;">
            <div class="logo-circle">
                <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772298452/logo_ate_kabayan_jtfqeg.jpg" alt="Logo">
            </div>
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

<div class="overlay" id="overlay"></div>

<main>
    <div class="preparing-card">
        <div class="cooking-visual"><i class="fa-solid fa-fire-burner"></i></div>
        <h1>Lulutuin na, Kabayan!</h1>
        <p style="color: #666;">Ang iyong order ay hinahanda na sa aming kusina.</p>
        <div class="eta-box">
            <span style="font-weight:bold; font-size:0.8rem; color:#888; text-transform:uppercase;">Estimated Time Remaining:</span>
            <div class="eta-time" id="eta-display">--:--</div>
        </div>
    </div>
</main>

<footer>
    <div class="footer-grid">
        <div class="footer-col">
            <div class="footer-logo"><img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772298452/logo_ate_kabayan_jtfqeg.jpg"></div>
            <p><i class="fa-solid fa-location-dot"></i> 1785 Evangelista St., Bangkal, Makati City</p>
        </div>
        <div class="footer-col">
            <h4 style="margin-bottom:10px;">SITEMAP</h4>
            <a href="homepage.php">Home</a>
            <a href="menu.php">Menu</a>
            <a href="cart.php">Cart</a>
        </div>
        <div class="footer-col">
            <h4 style="margin-bottom:10px;">SOCIALS</h4>
            <a href="#"><i class="fa-brands fa-facebook"></i> Ate Kabayan</a>
            <a href="#"><i class="fa-brands fa-instagram"></i> @kainanniatekabayan</a>
        </div>
    </div>
</footer>

<script>
    // --- BURGER MENU LOGIC ---
    const hamburgerBtn = document.getElementById('hamburger-btn');
    const sideNav = document.getElementById('side-nav');
    const overlayEl = document.getElementById('overlay');
    const closeBtnEl = document.getElementById('close-btn');

    hamburgerBtn.addEventListener('click', () => { 
        sideNav.classList.add('active'); 
        overlayEl.classList.add('active'); 
    });
    closeBtnEl.addEventListener('click', () => { 
        sideNav.classList.remove('active'); 
        overlayEl.classList.remove('active'); 
    });
    overlayEl.addEventListener('click', () => { 
        sideNav.classList.remove('active'); 
        overlayEl.classList.remove('active'); 
    });

    // --- CART BADGE COUNT ---
    fetch('get_cart_count.php').then(r=>r.json()).then(d=>{
        const b=document.getElementById('cart-badge');
        if(b){b.innerText=d.total_qty||0;b.style.display=(d.total_qty>0)?'flex':'none';}
    }).catch(()=>{});

    const orderId = "<?php echo $order['id']; ?>";
    const orderType = "<?php echo strtolower($order['order_type']); ?>"; // Kunin ang type
    let countdownInterval;

    function startCountdown(startTimeStr, prepMinutes) {
        if (!startTimeStr || prepMinutes == 0) {
            document.getElementById('eta-display').innerText = "Waiting...";
            return;
        }
        const startTime = new Date(startTimeStr.replace(/-/g, "/")).getTime();
        const endTime = startTime + (prepMinutes * 60 * 1000);

        if (countdownInterval) clearInterval(countdownInterval);
        
        countdownInterval = setInterval(() => {
            const now = new Date().getTime();
            const distance = endTime - now;
            if (distance <= 0) {
                clearInterval(countdownInterval);
                document.getElementById('eta-display').innerText = "READY!";
                return;
            }
            const mins = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const secs = Math.floor((distance % (1000 * 60)) / 1000);
            document.getElementById('eta-display').innerText = 
                (mins < 10 ? "0"+mins : mins) + ":" + (secs < 10 ? "0"+secs : secs);
        }, 1000);
    }

    function checkStatus() {
        fetch('check_order_status.php?id=' + orderId)
        .then(res => res.json())
        .then(data => {
            // Update countdown kung may bagong preparation data
            if (data.prep_start_time && data.prep_time) {
                startCountdown(data.prep_start_time, data.prep_time);
            }

            const currentStatus = data.status?.toLowerCase();

            // REDIRECTION LOGIC:
            // 1. Kung Pick Up at Completed na, rerekta sa tamang ratings page na rate.php
            if (currentStatus === 'completed' && orderType === 'pick up') {
                window.location.href = 'rate.php?id=' + orderId;
            } 
            // 2. Kung Delivery at On the Way na, lipat sa tracking page
            else if (['on the way', 'otw', 'dispatched'].includes(currentStatus)) {
                window.location.href = 'otw.php?id=' + orderId;
            }
        });
    }

    const initialStart = "<?php echo $order['prep_start_time'] ?? ''; ?>";
    const initialMins = <?php echo $order['prep_time'] ?? 0; ?>;
    if (initialStart && initialMins != 0) startCountdown(initialStart, initialMins);
    
    // I-check ang status bawat 5 segundo
    setInterval(checkStatus, 5000);
</script>

</body>
</html>