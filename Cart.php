<?php
session_start();
include "db_conn.php";

// 1. Check kung sino ang user (Para sa kaniya-kaniyang cart)
$is_logged_in = isset($_SESSION['user_id']);
$user_id = $is_logged_in ? $_SESSION['user_id'] : 'guest';

$fullname = "Guest User";
$profile_pic = "";
$discount_type = "None";

// 2. Kunin ang profile data mula sa database
if ($is_logged_in) {
    $sql = "SELECT full_name, profile_pic, discount_type FROM create_acc WHERE id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $user_data = mysqli_fetch_assoc($result);
        $fullname = $user_data['full_name'];
        $profile_pic = $user_data['profile_pic'];
        $discount_type = $user_data['discount_type'] ?? "None";
    }
}

// 3. --- DATABASE BRIDGE ---
$db_items = [];
if ($is_logged_in) {
    $cart_query = mysqli_query($conn, "SELECT item_name as name, price, image_url as img, qty FROM user_cart WHERE user_id = '$user_id'");
    while ($row = mysqli_fetch_assoc($cart_query)) {
        $db_items[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kainan ni Ate Kabayan - My Cart</title>
    <link rel="stylesheet" href="Cart.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Poppins:wght@300;400;600;700;800&family=Montserrat:wght@400;700;800&display=swap" rel="stylesheet">
    <style>
        .pop-card { background: #fff; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); cursor: pointer; transition: transform 0.2s; overflow: hidden; display: flex; flex-direction: column; }
        .pop-card:hover { transform: translateY(-3px); }
        .pop-img { width: 100%; height: 120px; object-fit: cover; background-color: #f0f0f0; }
        .pop-details { padding: 10px; flex-grow: 1; position: relative; }
        .pop-details h4 { font-size: 0.9rem; color: #d35400; margin-bottom: 5px; }
        .pop-details p { font-size: 0.8rem; color: #555; margin-bottom: 25px; line-height: 1.2; }
        .btn-mini-add { position: absolute; bottom: 10px; right: 10px; background: none; border: none; font-size: 1.5rem; color: #d35400; cursor: pointer; }
        #discount-row { color: #27ae60; font-weight: 700; display: none; }
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
            <?php if($is_logged_in): ?>
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
            <?php endif; ?>

            <a href="cart.php" class="cart-icon-btn active-cart">
                <i class="fa-solid fa-shopping-cart"></i>
                <span class="badge" id="cart-badge">0</span>
            </a>
            
            <div class="hamburger-menu" id="hamburger-btn">
                <i class="fa-solid fa-bars"></i>
            </div>
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
                <?php if($is_logged_in): ?><a href="Profile.php">(View Profile)</a><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="nav-links">
        <a href="homepage.php"><i class="fa-solid fa-house"></i> Home</a>
        <a href="menu.php"><i class="fa-solid fa-utensils"></i> Menu</a>
        <a href="orders.php"><i class="fa-solid fa-file-lines"></i> Orders</a>
        <a href="cart.php" class="active"><i class="fa-solid fa-cart-shopping"></i> Cart</a>
        <a href="reviews.php"><i class="fa-solid fa-star"></i> Reviews</a>
        <a href="About Us.php"><i class="fa-solid fa-book-open"></i> About Us</a>
        <a href="Contactus.php"><i class="fa-solid fa-phone"></i> Contact Us</a>
        <a href="logout.php" class="logout"><i class="fa-solid fa-arrow-right-from-bracket"></i> Log Out</a>
    </div>
</nav>

<div class="overlay" id="overlay"></div>

<main>
    <div class="page-header">
        <h1>Cart</h1>
        <p>KAINAN NI ATE KABAYAN - Bangkal, Makati City</p>
    </div>

    <div class="stepper-container">
        <div class="stepper">
            <div class="step completed"><div class="circle">1</div><span>Menu</span></div>
            <div class="line active"></div>
            <div class="step active"><div class="circle">2</div><span>Cart</span></div>
            <div class="line"></div>
            <div class="step"><div class="circle">3</div><span>Checkout</span></div>
        </div>
    </div>

    <div class="serving-container">
        <div class="serving-card">
            <div class="serving-icon"><i class="fa-solid fa-bell-concierge"></i></div>
            <div class="serving-info">
                <small>Serving time</small>
                <h3>Standard (15-30 mins)</h3>
                <a href="#">Change</a>
            </div>
        </div>
    </div>

    <div class="cart-list-container" id="cart-items-list">
        <div class="empty-msg">
            <i class="fa-solid fa-basket-shopping"></i>
            <p>Your cart is empty. <br> Add items from the Popular section below!</p>
        </div>
    </div>

    <div class="add-more-container">
        <button class="btn-add-items" id="btn-add-more" onclick="window.location.href='menu.php'">
            <i class="fa-solid fa-circle-plus"></i> Add more items
        </button>
    </div>

    <div class="popular-section">
        <div class="popular-header">
            <h3>Popular with your order</h3>
            <p>Other customers also bought these</p>
        </div>
        
        <div class="popular-grid">
            <div class="pop-card" onclick="addToCart('Shanghai', 135, 'https://res.cloudinary.com/dn38jxbeh/image/upload/v1773501411/Shanghai_etsffk.png')">
                <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1773501411/Shanghai_etsffk.png" alt="Shanghai" class="pop-img">
                <div class="pop-details">
                    <h4>PHP 135.00</h4>
                    <p>Shanghai</p>
                    <button class="btn-mini-add"><i class="fa-solid fa-circle-plus"></i></button>
                </div>
            </div>

            <div class="pop-card" onclick="addToCart('Coke Mismo', 30, 'https://res.cloudinary.com/dn38jxbeh/image/upload/v1773501339/Coke_Misskona_mzsigp.png')">
                <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1773501339/Coke_Misskona_mzsigp.png" alt="Coke Mismo" class="pop-img">
                <div class="pop-details">
                    <h4>PHP 30.00</h4>
                    <p>Coke Mismo</p>
                    <button class="btn-mini-add"><i class="fa-solid fa-circle-plus"></i></button>
                </div>
            </div>

            <div class="pop-card" onclick="addToCart('Tokwa\'t Lechon', 120, 'https://res.cloudinary.com/dn38jxbeh/image/upload/v1773501436/Tokwat_Lechon_l69imm.png')">
                <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1773501436/Tokwat_Lechon_l69imm.png" alt="Tokwa't Lechon" class="pop-img">
                <div class="pop-details">
                    <h4>PHP 120.00</h4>
                    <p>Tokwa't Lechon</p>
                    <button class="btn-mini-add"><i class="fa-solid fa-circle-plus"></i></button>
                </div>
            </div>

            <div class="pop-card" onclick="addToCart('Lemon Cucumber', 25, 'https://res.cloudinary.com/dn38jxbeh/image/upload/v1773501376/Lemon_Cucumber_Juice_taon1v.png')">
                <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1773501376/Lemon_Cucumber_Juice_taon1v.png" alt="Lemon Cucumber" class="pop-img">
                <div class="pop-details">
                    <h4>PHP 25.00</h4>
                    <p>Lemon Cucumber</p>
                    <button class="btn-mini-add"><i class="fa-solid fa-circle-plus"></i></button>
                </div>
            </div>

            <div class="pop-card" onclick="addToCart('Buttered Chicken', 220, 'https://res.cloudinary.com/dn38jxbeh/image/upload/v1773501333/Buttered_Chicken_dqy0sx.png')">
                <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1773501333/Buttered_Chicken_dqy0sx.png" alt="Buttered Chicken" class="pop-img">
                <div class="pop-details">
                    <h4>PHP 220.00</h4>
                    <p>Buttered Chicken</p>
                    <button class="btn-mini-add"><i class="fa-solid fa-circle-plus"></i></button>
                </div>
            </div>
        </div>
    </div>

    <div class="summary-container">
        <div class="summary-content">
            <div class="row subtotal">
                <span>Subtotal</span>
                <span class="price-text">PHP <span id="subtotal-display">0.00</span></span>
            </div>

            <div class="row vat">
                <span>VAT (12%)</span>
                <span class="price-text">PHP <span id="vat-display">0.00</span></span>
            </div>

            <div class="row discount" id="discount-row">
                <span>Senior/PWD Discount</span>
                <span class="price-text">- PHP <span id="discount-display">0.00</span></span>
            </div>
            
            <div class="divider-line"></div>

            <div class="row total">
                <div class="total-labels">
                    <h3>Total <span>(incl. fees and tax)</span></h3>
                    <a href="#">See summary</a>
                </div>
                <div class="total-values">
                    <h3>PHP <span id="total-display">0.00</span></h3>
                </div>
            </div>

            <button class="btn-checkout" id="btn-proceed-checkout" onclick="proceedToCheckout()">
                  Review payment and address
            </button>
        </div>
    </div>
</main>

<footer>
    <div class="footer-container">
        <div class="footer-col brand">
            <div class="logo-circle large"><img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772298452/logo_ate_kabayan_jtfqeg.jpg" style="width:100%; border-radius:50%;"></div>
            <div class="contact-info">
                <p><i class="fa-solid fa-clock"></i> OPEN DAILY (10AM - 3AM)</p>
                <p><i class="fa-solid fa-phone"></i> (0921) 918 6057</p>
                <p><i class="fa-solid fa-location-dot"></i> 1785 Evangelista St. Bangkal, Makati City</p>
            </div>
        </div>
        <div class="footer-col links">
            <h4>SITEMAP</h4>
            <ul>
                <li><a href="homepage.php">Home</a></li>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="homepage.php#reserve">Reserve a Table</a></li>
            </ul>
        </div>
        <div class="footer-col social">
            <h4>SOCIAL MEDIA</h4>
            <ul>
                <li><a href="#"><i class="fa-brands fa-facebook"></i> Kainan ni Ate Kabayan</a></li>
                <li><a href="#"><i class="fa-brands fa-instagram"></i> @kainanniatekabayan</a></li>
            </ul>
        </div>
    </div>
    <div class="copyright">
        <p>&copy; 2026 Kainan ni Ate Kabayan. All Rights Reserved.</p>
    </div>
</footer>

<script>
    const isLogged = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
    const currentUserId = "<?php echo $user_id; ?>";
    const cartKey = isLogged ? 'cart_' + currentUserId : 'cart_guest';

    const dbItems = <?php echo json_encode($db_items); ?>;
    if (isLogged && dbItems.length > 0) {
        localStorage.setItem(cartKey, JSON.stringify(dbItems));
    }

    localStorage.setItem('userDiscountType', '<?php echo $discount_type; ?>');

    function proceedToCheckout() {
        const cart = JSON.parse(localStorage.getItem(cartKey)) || [];
        if (cart.length === 0) {
            alert("Naku Kabayan, lagyan mo muna ng order ang cart mo!");
        } else {
            localStorage.setItem('myCart', JSON.stringify(cart));
            window.location.href = 'Checkout.php';
        }
    }

    // Side Nav Logic
    const hamburgerBtn = document.getElementById('hamburger-btn');
    const sideNav = document.getElementById('side-nav');
    const closeBtn = document.getElementById('close-btn');
    const overlay = document.getElementById('overlay');

    hamburgerBtn.addEventListener('click', () => {
        sideNav.classList.add('active');
        overlay.classList.add('active');
    });

    closeBtn.addEventListener('click', () => {
        sideNav.classList.remove('active');
        overlay.classList.remove('active');
    });

    overlay.addEventListener('click', () => {
        sideNav.classList.remove('active');
        overlay.classList.remove('active');
    });

    // --- CART BADGE COUNT ---
    fetch('get_cart_count.php').then(r=>r.json()).then(d=>{
        const b=document.getElementById('cart-badge');
        if(b){b.innerText=d.total_qty||0;b.style.display=(d.total_qty>0)?'flex':'none';}
    }).catch(()=>{});
</script>

<script src="Cart.js"></script>
</body>
</html>