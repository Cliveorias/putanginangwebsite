<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Kainan ni Ate Kabayan</title>
    <!-- Gagamit ng parehong CSS para sa consistency[cite: 9] -->
    <link rel="stylesheet" href="homepage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Karagdagang style para sa Guest Buttons */
        .btn-login-header {
            background: var(--white);
            color: var(--highlight-orange);
            padding: 8px 18px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 800;
            font-size: 0.85rem;
            transition: 0.3s;
            border: 2px solid transparent;
        }
        .btn-login-header:hover {
            background: transparent;
            color: var(--white);
            border-color: var(--white);
        }
        .btn-signup-header {
            background: var(--order-now-red);
            color: var(--white);
            padding: 8px 18px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 800;
            font-size: 0.85rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>

<header>
    <div class="header-container">
        <div class="logo">
            <div class="logo-circle">
                <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772298452/logo_ate_kabayan_jtfqeg.jpg" alt="Logo">
            </div>
            <h2>KAINAN NI ATE KABAYAN</h2>
        </div>

        <nav class="desktop-nav">
            <a href="index.html" class="active">Home</a>
            <a href="menu.php">Menu</a> <!-- Guest can still browse menu -->
            <a href="About Us.php">About Us</a>
            <a href="Contactus.php">Contact</a>
        </nav>
        
        <div class="header-actions">
            <!-- Guest Buttons sa halip na Profile -->
            <a href="login.html" class="btn-login-header">LOG IN</a>
            <a href="register.html" class="btn-signup-header">SIGN UP</a>

            <a href="cart.php" class="cart-icon-btn">
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
            <div class="profile-img"><i class="fa-solid fa-user"></i></div>
            <div class="profile-text">
                <h3>Hello, Guest!</h3>
                <p style="font-size: 0.75rem; color: #eee;">Log in para makapag-order.</p>
            </div>
        </div>
    </div>

    <div class="nav-links">
        <a href="index.html" class="active"><i class="fa-solid fa-house"></i> Home</a>
        <a href="menu.php"><i class="fa-solid fa-utensils"></i> Browse Menu</a>
        <a href="About Us.php"><i class="fa-solid fa-book-open"></i> About Us</a>
        <a href="Contactus.php"><i class="fa-solid fa-phone"></i> Contact Us</a>
        <hr style="border: 0.5px solid rgba(255,255,255,0.2); margin: 10px 0;">
        <a href="login.html" style="background: #C92C1C;"><i class="fa-solid fa-right-to-bracket"></i> Log In</a>
    </div>
</nav>

<div class="overlay" id="overlay"></div>

<section class="hero">
    <div class="hero-text">
         <div class="hero-buttons">
            <a href="menu.php" class="btn-order">TINGNAN ANG MENU!</a>
         </div>
    </div>
</section>

<!-- BEST SELLERS (Static for Landing Page) -->
<section id="menu" class="menu-section">
    <div class="menu-orange-banner">
        <h2 class="banner-title">MGA BEST-SELLERS NI ATE</h2>
    </div>
    <div class="container menu-container-content">
        <h3 class="section-subtitle-compact">SILIPIN ANG MGA <span class="best-sellers">PABORITO</span> NG BAYAN:</h3>

        <div class="menu-scroll-wrapper">
            <!-- Porksilog Card[cite: 7] -->
            <div class="menu-card-scroll">
                <div class="card-image-circle">
                    <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772298630/PorkSilog_wpfoym.png" alt="Porksilog">
                </div>
                <div class="card-content">
                    <div class="title-price"><h3>PORKSILOG</h3><span class="price-badge">₱130</span></div>
                    <p class="desc">Crispy porkchop with sinangag rice and fried egg.</p>
                    <div class="rating">⭐⭐⭐⭐⭐</div>
                    <div class="card-actions">
                        <button class="btn-add-cart">Add to Cart! <i class="fa-solid fa-cart-shopping"></i></button>
                    </div>
                </div>
            </div>

            <!-- Goto Overload[cite: 7] -->
            <div class="menu-card-scroll">
                <div class="card-image-circle">
                    <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772299118/Goto_Overload_xupge7.png" alt="Goto Overload">
                </div>
                <div class="card-content">
                    <div class="title-price"><h3>GOTO OVERLOAD</h3><span class="price-badge">₱150</span></div>
                    <p class="desc">Sabaw na may overload twalya, balat, isaw, at karne.</p>
                    <div class="rating">⭐⭐⭐⭐⭐</div>
                    <div class="card-actions">
                        <button class="btn-add-cart">Add to Cart! <i class="fa-solid fa-cart-shopping"></i></button>
                    </div>
                </div>
            </div>

            <!-- Sisig Barkada[cite: 7] -->
            <div class="menu-card-scroll">
                <div class="card-image-circle">
                    <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772299076/Sisig_Barkada_nztbzh.png" alt="Sisig">
                </div>
                <div class="card-content">
                    <div class="title-price"><h3>SISIG BARKADA</h3><span class="price-badge">₱185</span></div>
                    <p class="desc">Sizzling pork sisig with egg for sharing barkada.</p>
                    <div class="rating">⭐⭐⭐⭐⭐</div>
                    <div class="card-actions">
                        <button class="btn-add-cart">Add to Cart! <i class="fa-solid fa-cart-shopping"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section[cite: 7] -->
<section class="features-section">
    <div class="features-container">
        <div class="feature-card">
            <div class="feature-icon-box"><img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772299469/Motor_Driver_Homepage_ieqima.png"></div>
            <div class="feature-content"><h3>Fast Delivery</h3><p>Mabilis at mainit na makakarating ang paborito mong meal!</p></div>
        </div>
        <div class="feature-card">
            <div class="feature-icon-box"><img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1775261436/Alagang_Bayan_logo_pic_bj3fas.png"></div>
            <div class="feature-content"><h3>Alagang Kabayan</h3><p>Laging may ngiti at malasakit mula sa aming team.</p></div>
        </div>
    </div>
</section>

<footer id="contact">
    <div class="footer-content">
        <div class="footer-section contact-info">
            <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772298452/logo_ate_kabayan_jtfqeg.jpg" alt="Logo" class="footer-logo">
            <div class="details">
                <p><i class="fas fa-clock"></i> OPEN DAILY (10AM - 3AM)</p>
                <p><i class="fas fa-map-marker-alt"></i> 1785 Evangelista St., Bangkal, Makati City</p>
            </div>
        </div>
        <div class="footer-section sitemap">
            <h3>SITEMAP</h3>
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="login.html">Log In</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom"><p>© 2026 Kainan ni Ate Kabayan. All Right Reserved.</p></div>
</footer>

<!-- JavaScript Setup -->
<script>
    // Dahil guest page ito, sini-set natin ang key sa guest cart
    const isLoggedIn = false;
    const cartKey = 'cart_guest';
</script>
<script src="homepage.js"></script>

<div id="toast" style="visibility: hidden; min-width: 250px; background-color: #333; color: #fff; text-align: center; border-radius: 50px; padding: 16px; position: fixed; z-index: 1000; left: 50%; bottom: 30px; transform: translateX(-50%); transition: 0.3s opacity;">
    Added to guest cart!
</div>

</body>
</html>