<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kainan ni Ate Kabayan - About Us</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Patrick+Hand&family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="Aboutus.css">
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
            <a href="OrderConfirmation.php">Orders</a>
            <a href="ratings.php">Reviews</a>
            <a href="About Us.php" class="active">About Us</a>
            <a href="Contactus.php">Contact</a>
        </nav>
        
        <div class="header-actions">
            <?php if($is_logged_in): ?>
            <a href="Profile.php" class="header-profile-desktop" style="text-decoration: none; display: flex; align-items: center; gap: 10px;">
                <div class="profile-img-small">
                    <?php if (!empty($display_pic)): ?>
                        <img src="<?php echo htmlspecialchars($display_pic); ?>" alt="Profile">
                    <?php else: ?>
                        <i class="fa-solid fa-user"></i>
                    <?php endif; ?>
                </div>
                <span class="header-user-name" style="color: #fff; font-weight: bold;">HI, <?php echo strtoupper(explode(' ', trim($display_name))[0]); ?>!</span>
            </a>
            <?php endif; ?>
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
        <div class="close-btn" id="close-btn">
            <i class="fa-solid fa-xmark"></i>
        </div>
        <div class="profile-info">
            <div class="profile-img">
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="profile-text">
                <h3>Juan Dela Cruz</h3>
                <a href="Profile.html">(View Profile)</a>
            </div>
        </div>
    </div>

    <div class="nav-links">
        <a href="homepage.html"><i class="fa-solid fa-house"></i> Home</a>
        <a href="menu.html"><i class="fa-solid fa-utensils"></i> Menu</a>
        <a href="orders.html"><i class="fa-solid fa-file-lines"></i> Orders</a>
        <a href="cart.html"><i class="fa-solid fa-cart-shopping"></i> Cart</a>
        <a href="reviews.html"><i class="fa-solid fa-star"></i> Reviews</a>
        <a href="About Us.html" class="active"><i class="fa-solid fa-book-open"></i> About Us</a>
        <a href="Contactus.html"><i class="fa-solid fa-phone"></i> Contact Us</a>
        <a href="logout.html" class="logout"><i class="fa-solid fa-arrow-right-from-bracket"></i> Log Out</a>
    </div>
</nav>

<div class="overlay" id="overlay"></div>

    <section class="hero">
        <div class="hero-text">
            <h1 class="font-display">
                WHY DINE <br>
                <span class="highlight-orange">WITH US?</span>
            </h1>
            <p class="font-handwriting hero-tagline">"Lasang Bahay, Pusong Kabayan!"</p>

            <div class="story-box">
                <h3 class="story-title">DISCOVER OUR STORY</h3>
                <p>Ang kwento ng <strong>Kainan ni Ate Kabayan</strong> ay nagsimula sa puso ng <strong>Batangas</strong>, gamit ang special recipe ng aming yumaong Tatay. Sumikat ito noong 2022 sa <strong>"Walastik ni Kabayan"</strong> ng aking kapatid—ang tawagang "Kabayan" ay tatak Batangueño ng pagiging malapit.</p>
                <br>
                <p>Inspired by this success, itinayo ko ang <strong>Walastik Pares ni Ate Kabayan (WPAK)</strong>. But we wanted to give you more! Kaya nag-evolve kami into <strong>Kainan ni Ate Kabayan</strong> para mas malawak ang mai-serve sa inyo.</p>
                <br>
                <p>Ngayon, hindi lang kami tungkol sa pares—may authentic <strong>Gotong Batangas, Lugaw, Silogs, Sizzlings, at Chowfordabowls</strong> na rin. Mula sa recipe ni Tatay hanggang sa inyong hapag-kainan, tuloy-tuloy ang alagang Kabayan!</p>
            </div>
        </div>

        <div class="hero-images">
            <div class="floating-food">
                <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1773501308/ABOUT_US_PIC_CIRC_cqreh6.png" alt="Dining Vibe">
            </div>
            
            <div class="polaroid polaroid-1">
                <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772299840/Homepage_pic1_cffqvi.jpg" alt="Group Dining">
            </div>
            
            <div class="polaroid polaroid-2">
                <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772299861/Homepage_pic2_v3nsaf.jpg" alt="Ate Kabayan">
            </div>
        </div>
    </section>

    <section class="crew-section" style="padding: 4rem 1rem; background-color: #fff; text-align: center;">
        <h2 class="font-display" style="font-size: 2.5rem; text-transform: uppercase; margin-bottom: 2rem;">
            MEET <span style="color: #FFBD59;">ATE KABAYAN'S</span> CREW!
        </h2>
        
        <div style="max-width: 800px; margin: 0 auto 2rem;">
            <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1773501311/Ate_kabayan_Crew_johpxv.png" alt="Ate Kabayan Crew" style="width: 100%; border-radius: 20px; box-shadow: 0 10px 20px rgba(0,0,0,0.1); display: block;">
        </div>

        <div style="max-width: 800px; margin: 0 auto; text-align: left; font-family: 'Poppins', sans-serif; color: #333; line-height: 1.6; font-size: 1rem;">
            <p style="margin-bottom: 1rem;">Welcome sa ating tahanan! Hindi lang kami basta staff, kami ay pamilyang handang mag-serve ng Alagang Kabayan.</p>
            <p style="margin-bottom: 1rem;">Leading our kitchen is <strong>Ate Kabayan Marilou</strong> para sa authentic na timpla ng ating mga paborito, habang si <strong>Kuya Kabayan David</strong> naman ang pillar ng ating daily operations. Si Charlie ang nagpapanatili ng ating connection online, at ang buong Crew naman ang sisiguro na busog kayo sa sarap at serbisyo. Sa bawat higop ng sabaw at kagat ng sizzling, damang-dama ang pusong Pinoy!</p>
            <p>Basta dito, parang nasa bahay ka lang. Tikman ang sarap ng sariling atin!</p>
        </div>
    </section>

    <footer id="contact">
        <div class="footer-content">
            <div class="footer-section contact-info">
                <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772298452/logo_ate_kabayan_jtfqeg.jpg" alt="Kainan ni Ate Kabayan Logo" class="footer-logo">
                <div class="details">
                    <p><i class="fas fa-clock"></i> OPEN DAILY (10AM - 3AM)</p>
                    <p><i class="fas fa-phone-alt"></i> (0921) 910 6057</p>
                    <p><i class="fas fa-map-marker-alt"></i> 1785 Evangelista St., Bangkal, Makati City</p>
                </div>
            </div>

            <div class="footer-section sitemap">
                <h3>SITEMAP</h3>
                <ul>
                    <li><a href="homepage.html">Home</a></li>
                    <li><a href="menu.html">Menu</a></li>
                    <li><a href="homepage.html#reserve">Reserve a Table</a></li>
                    <li><a href="About Us.html">About Us</a></li>
                    <li><a href="Contactus.html">Contact Us</a></li>
                </ul>
            </div>

            <div class="footer-section social-media">
                <h3>SOCIAL MEDIA</h3>
                <ul>
                    <li><a href="https://www.facebook.com/kainanniatekabayan"><i class="fab fa-facebook-f"></i> Kainan ni Ate Kabayan</a></li>
                    <li><a href="https://www.instagram.com/kainanniatekabayan/"><i class="fab fa-instagram"></i> @Kainan ni Ate Kabayan  </a></li>
                    <li><a href="https://share.google/I9ubNtooj7WKodVWl"><i class="fab fa-google"></i> Kainan ni Ate Kabayan</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>© 2022 Kainan ni Ate Kabayan. All Right Reserved.</p>
        </div>
    </footer>

    <script src="Aboutus.js"></script>
</body>
</html>