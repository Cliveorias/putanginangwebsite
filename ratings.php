<?php
session_start();
include "db_conn.php";

// User data para sa header
$is_logged_in = isset($_SESSION['user_id']);
$fullname = "Guest";
$profile_pic = "";

if ($is_logged_in) {
    $uid = $_SESSION['user_id'];
    $u_sql = "SELECT full_name, profile_pic FROM create_acc WHERE id = '$uid'";
    $u_res = mysqli_query($conn, $u_sql);
    if ($u_res && $u_row = mysqli_fetch_assoc($u_res)) {
        $fullname = $u_row['full_name'];
        $profile_pic = $u_row['profile_pic'];
    }
}

// 1. Kunin ang Average Rating at Total Count
$stat_sql = "SELECT AVG(rating) as avg_score, COUNT(*) as total FROM ratings";
$stat_res = mysqli_query($conn, $stat_sql);
$stats = mysqli_fetch_assoc($stat_res);
$avg_score = number_format($stats['avg_score'] ?? 0, 1);
$total_reviews = $stats['total'];

// 2. Kunin ang bilang ng bawat star (para sa progress bars)
$counts = [5=>0, 4=>0, 3=>0, 2=>0, 1=>0];
$count_sql = "SELECT rating, COUNT(*) as count FROM ratings GROUP BY rating";
$count_res = mysqli_query($conn, $count_sql);
while($row = mysqli_fetch_assoc($count_res)) {
    $counts[$row['rating']] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ratings and Reviews - Ate Kabayan</title>
    <link rel="stylesheet" href="homepage.css">
    <link rel="stylesheet" href="ratings.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Poppins:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* Karagdagang style para sa default avatar */
        .user-avatar-container {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 2px solid #ddd;
        }
        .user-avatar-container i {
            font-size: 24px;
            color: #aaa;
        }
        .user-avatar-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
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
            <a href="ratings.php" class="active">Reviews</a>
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
                <?php if($is_logged_in): ?><a href="Profile.php">(View Profile)</a><?php endif; ?>
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

<main class="container">
    <h2 class="section-title">RATINGS AND REVIEW</h2>

    <section class="rating-summary">
        <div class="score-box">
            <div class="stars-display">
                <?php 
                for($i=1; $i<=5; $i++) {
                    if($i <= round($avg_score)) {
                        echo "<i class='fa-solid fa-star' style='color: #FFD700;'></i>";
                    } else {
                        echo "<i class='fa-regular fa-star'></i>";
                    }
                } 
                ?>
            </div>
            <div class="big-score"><?php echo $avg_score; ?></div>
            <div class="total-text"><?php echo $total_reviews; ?> Reviews</div>
        </div>

        <div class="progress-container">
            <?php for($i=5; $i>=1; $i--): 
                $percent = ($total_reviews > 0) ? ($counts[$i] / $total_reviews) * 100 : 0;
            ?>
            <div class="bar-row">
                <span><?php echo $i; ?></span>
                <div class="bar-bg"><div class="bar-fill" style="width: <?php echo $percent; ?>%;"></div></div>
            </div>
            <?php endfor; ?>
        </div>
    </section>

    <div class="filter-wrapper">
        <button class="filter-btn active" onclick="filterReviews('all')">All</button>
        <button class="filter-btn" onclick="filterReviews(5)">5 Stars</button>
        <button class="filter-btn" onclick="filterReviews(4)">4 Stars</button>
        <button class="filter-btn" onclick="filterReviews(3)">3 Stars</button>
        <button class="filter-btn" onclick="filterReviews(2)">2 Stars</button>
        <button class="filter-btn" onclick="filterReviews(1)">1 Star</button>
    </div>

    <section class="reviews-feed" id="reviews-feed">
        <?php
        $query = "SELECT r.*, c.full_name, c.profile_pic FROM ratings r 
                  JOIN create_acc c ON r.user_id = c.id ORDER BY r.date_submitted DESC";
        $result = mysqli_query($conn, $query);
        while($row = mysqli_fetch_assoc($result)):
        ?>
        <div class="review-card" data-rating="<?php echo $row['rating']; ?>">
            <div class="review-header">
                <!-- Inayos na Profile Pic Logic -->
                <div class="user-avatar-container">
                    <?php if (!empty($row['profile_pic'])): ?>
                        <img src="<?php echo htmlspecialchars($row['profile_pic']); ?>" alt="Profile">
                    <?php else: ?>
                        <i class="fa-solid fa-user"></i>
                    <?php endif; ?>
                </div>
                
                <div class="user-meta">
                    <h4><?php echo htmlspecialchars($row['full_name']); ?></h4>
                    <div class="card-stars">
                        <?php for($i=1; $i<=5; $i++) echo "<i class='fa-solid fa-star " . ($i <= $row['rating'] ? "active" : "") . "'></i>"; ?>
                    </div>
                </div>
                <span class="review-date"><?php echo date('M d, Y', strtotime($row['date_submitted'])); ?></span>
            </div>
            <p class="review-text"><?php echo htmlspecialchars($row['feedback']); ?></p>
            <?php if(!empty($row['feedback_image'])): ?>
                <img src="<?php echo $row['feedback_image']; ?>" class="attached-img">
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    </section>
</main>

<footer>
    <div class="footer-grid">
        <div class="footer-col">
            <div class="footer-logo"><img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772298452/logo_ate_kabayan_jtfqeg.jpg"></div>
            <p><i class="fa-solid fa-clock"></i> OPEN DAILY (10AM - 3AM)</p>
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
            <a href="#"><i class="fa-brands fa-facebook"></i> Ate Kabayan</a>
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
<script src="ratings.js"></script>
</body>
</html>