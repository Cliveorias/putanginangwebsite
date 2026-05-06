<?php
session_start();
include "db_conn.php"; // Siguraduhing tama ang path ng db_conn mo

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['id'] ?? 0;

// Fetch user data para sa sidebar
$user_sql = "SELECT full_name, profile_pic FROM create_acc WHERE id = '$user_id'";
$user_res = mysqli_query($conn, $user_sql);
$user_data = mysqli_fetch_assoc($user_res);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Us - Ate Kabayan</title>
    <link rel="stylesheet" href="homepage.css">
    <link rel="stylesheet" href="rate.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Poppins:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
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
    <div class="rating-container">
        <div class="hero-image">
            <div class="circle-bg">
                <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1740571343/rating-hero_vjqyzh.png" alt="Happy Eating">
            </div>
        </div>

        <h1>Kabayan, Your food has arrived!</h1>
        <p class="subtitle">Maraming Salamat sa iyong order, tara kain na!</p>

        <form action="submit_rating.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
            
            <h3>Kamusta ang aming serbisyo?</h3>
            
            <div class="stars" id="star-rating">
                <i class="fa-solid fa-star" data-value="1"></i>
                <i class="fa-solid fa-star" data-value="2"></i>
                <i class="fa-solid fa-star" data-value="3"></i>
                <i class="fa-solid fa-star" data-value="4"></i>
                <i class="fa-solid fa-star" data-value="5"></i>
                <input type="hidden" name="rating" id="rating-value" required>
            </div>

            <div class="feedback-box">
                <textarea name="feedback" placeholder="Write your feeback here..." required></textarea>
                
                <div class="feedback-footer">
                    <label for="image-upload" class="image-upload-btn">
                        <i class="fa-solid fa-camera"></i>
                        <span>Add Photo</span>
                    </label>
                    <input type="file" id="image-upload" name="feedback_image" accept="image/*" style="display:none;" onchange="previewImage(this)">
                    
                    <button type="submit" class="btn-submit">SUBMIT</button>
                </div>

                <div id="image-preview-container" style="display:none;">
                    <img id="image-preview" src="#" alt="Preview">
                    <i class="fa-solid fa-circle-xmark remove-img" onclick="removeImage()"></i>
                </div>
            </div>
        </form>
    </div>
</main>

<footer>
    <div class="footer-grid">
        <div class="footer-col">
            <div class="footer-logo"><img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772298452/logo_ate_kabayan_jtfqeg.jpg"></div>
            <p><i class="fa-solid fa-clock"></i> OPEN DAILY (10AM - 3AM)</p>
            <p><i class="fa-solid fa-phone"></i> (0921) 910 6057</p>
            <p><i class="fa-solid fa-location-dot"></i> 1785 Evangelista St., Bangkal, Makati City</p>
        </div>
        <div class="footer-col">
            <h4>SITEMAP</h4>
            <a href="homepage.php">Home</a>
            <a href="menu.php">Menu</a>
            <a href="cart.php">Cart</a>
        </div>
        <div class="footer-col">
            <h4>SOCIAL MEDIA</h4>
            <a href="#"><i class="fa-brands fa-facebook"></i> Kainan ni Ate Kabayan</a>
            <a href="#"><i class="fa-brands fa-instagram"></i> @kainanniatekabayan</a>
        </div>
    </div>
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
</script>
<script src="rate.js"></script>
</body>
</html>