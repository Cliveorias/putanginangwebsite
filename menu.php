<?php
session_start();
include "db_conn.php";

// 1. Check kung sino ang user (Para sa kaniya-kaniyang cart badge at profile)[cite: 8, 10]
$is_logged_in = isset($_SESSION['user_id']);
$user_id = $is_logged_in ? $_SESSION['user_id'] : 'guest';

$fullname = "Guest User";
$profile_pic = "";

// 2. Kunin ang data ng user mula sa database kung naka-login[cite: 8, 10]
if ($is_logged_in) {
    $sql = "SELECT full_name, profile_pic FROM create_acc WHERE id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $user_data = mysqli_fetch_assoc($result);
        $fullname = $user_data['full_name'];
        $profile_pic = $user_data['profile_pic'];
    }
}

// --- DYNAMIC RATINGS LOGIC ---[cite: 8, 10]
$rating_sql = "SELECT AVG(rating) as avg_rate, COUNT(id) as total FROM ratings";
$rating_result = mysqli_query($conn, $rating_sql);
$display_avg = "0.0";
$display_total = 0;

if ($rating_result) {
    $rating_row = mysqli_fetch_assoc($rating_result);
    if ($rating_row['total'] > 0) {
        $display_avg = number_format($rating_row['avg_rate'], 1);
        $display_total = $rating_row['total'];
    }
}

// --- DYNAMIC MENU LOGIC PARA SA MENU MANAGEMENT ---[cite: 8, 9]
// In-update para kunin ang lahat ng items para hindi mawala sa view ng customer[cite: 8, 9]
$menu_sql = "SELECT * FROM menu_items ORDER BY category DESC";
$menu_result = mysqli_query($conn, $menu_sql);

// Kunin ang lahat ng unique categories para sa filter buttons[cite: 8, 9]
$cat_sql = "SELECT DISTINCT category FROM menu_items";
$cat_result = mysqli_query($conn, $cat_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kainan ni Ate Kabayan - Menu</title>
    
    <link rel="stylesheet" href="homepage.css">
    <link rel="stylesheet" href="menu-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans+Code:ital,wght@0,300..800;1,300..800&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Pacifico&family=Patrick+Hand&family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <style>
        .profile-img-small { width: 35px; height: 35px; background: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid #ddd; }
        .profile-img-small i { color: #f39c12; font-size: 18px; }
        .profile-img-small img { width: 100%; height: 100%; object-fit: cover; }
        .profile-info .profile-img { width: 80px; height: 80px; background: #eee; border-radius: 50%; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .profile-info .profile-img i { font-size: 40px; color: #aaa; }

        /* --- STYLES PARA SA NOT AVAILABLE ITEMS ---[cite: 11] */
        .card-disabled {
            filter: grayscale(0.8);
            opacity: 0.7;
            pointer-events: none; /* Hindi maki-click ang card content */
        }
        .btn-disabled {
            background-color: #999 !important;
            cursor: not-allowed !important;
            box-shadow: none !important;
        }
        .not-available-label {
            color: #e74c3c;
            font-weight: 800;
            font-size: 0.8rem;
            text-transform: uppercase;
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
            <a href="menu.php" class="active">Menu</a>
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
        <a href="menu.php" class="active"><i class="fa-solid fa-utensils"></i> Menu</a>
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
        <section class="top-bar">
            <div class="search-container">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="searchInput" placeholder="Search Menu" autocomplete="off">
                <div id="searchResults" class="search-dropdown"></div>
            </div>
            <a href="ratings.php" class="rating-badge" style="text-decoration: none; color: inherit; cursor: pointer;">
                <div class="stars"><i class="fa-solid fa-star" style="color: #FFD700;"></i><i class="fa-solid fa-star" style="color: #FFD700;"></i><i class="fa-solid fa-star" style="color: #FFD700;"></i><i class="fa-solid fa-star" style="color: #FFD700;"></i><i class="fa-solid fa-star" style="color: #FFD700;"></i></div>
                <span><?php echo $display_avg; ?> (<?php echo $display_total; ?><?php echo ($display_total >= 100) ? '+' : ''; ?> Ratings)</span>
            </a>
        </section>

        <section class="categories-section">
            <div class="category-wrapper" id="categoryContainer">
                <button class="category-btn active" data-filter="all">All</button>
                <?php while($cat = mysqli_fetch_assoc($cat_result)): ?>
                    <button class="category-btn" data-filter="<?php echo strtolower($cat['category']); ?>">
                        <?php echo strtoupper($cat['category']); ?>
                    </button>
                <?php endwhile; ?>
            </div>
        </section>

        <div class="menu-grid" id="menuGrid">
            <?php if(mysqli_num_rows($menu_result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($menu_result)): 
                    $is_available = ($row['availability'] == 1); // Check status ng ulam[cite: 8, 9]
                ?>
                    <div class="menu-card <?php echo !$is_available ? 'card-disabled' : ''; ?>" data-category="<?php echo strtolower($row['category']); ?>">
                        <div class="card-img-container">
                            <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                        </div>
                        <div class="card-details">
                            <div class="card-header">
                                <h3><?php echo strtoupper($row['name']); ?></h3>
                                <!-- Conditional Price display[cite: 11] -->
                                <?php if($is_available): ?>
                                    <span class="price">₱<?php echo number_format($row['price'], 0); ?></span>
                                <?php else: ?>
                                    <span class="not-available-label">NOT AVAILABLE</span>
                                <?php endif; ?>
                            </div>
                            <p class="desc"><?php echo htmlspecialchars($row['description']); ?></p>
                            <div class="stars">
                                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                            </div>
                            <div class="card-actions">
                                <?php if($is_available): ?>
                                    <button class="btn-add-cart" onclick="addToCart('<?php echo addslashes($row['name']); ?>', <?php echo $row['price']; ?>, '<?php echo htmlspecialchars($row['image_url']); ?>')">
                                        Add to Cart! <i class="fa-solid fa-cart-shopping"></i>
                                    </button>
                                <?php else: ?>
                                    <!-- Disabled button para hindi ma-order[cite: 11] -->
                                    <button class="btn-add-cart btn-disabled" disabled style="pointer-events: auto;">
                                        Out of Stock <i class="fa-solid fa-ban"></i>
                                    </button>
                                <?php endif; ?>
                                <button class="btn-fav"><i class="fa-regular fa-heart"></i></button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="grid-column: 1/-1; text-align: center; padding: 50px;">Paumanhin, Kabayan! Walang pagkain sa ngayon.</p>
            <?php endif; ?>
        </div> 
    </main>

    <div id="toast" style="visibility: hidden; min-width: 250px; background-color: #333; color: #fff; text-align: center; border-radius: 50px; padding: 16px; position: fixed; z-index: 1000; left: 50%; bottom: 30px; transform: translateX(-50%); transition: 0.3s;">Item added to cart!</div>
<footer id="contact">
        <div class="footer-content">
            <div class="footer-section contact-info">
                <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772298452/logo_ate_kabayan_jtfqeg.jpg" alt="Logo" class="footer-logo">
                <div class="details">
                    <p><i class="fas fa-clock"></i> OPEN DAILY (10AM - 3AM)</p>
                    <p><i class="fas fa-phone-alt"></i> (0921) 910 6057</p>
                    <p><i class="fas fa-map-marker-alt"></i> 1785 Evangelista St., Bangkal, Makati City</p>
                </div>
            </div>
            <div class="footer-section sitemap">
                <h3>SITEMAP</h3>
                <ul>
                    <li><a href="homepage.php">Home</a></li>
                    <li><a href="menu.php">Menu</a></li>
                    <li><a href="homepage.php#reserve">Reserve a Table</a></li>
                    <li><a href="About Us.php">About Us</a></li>
                    <li><a href="Contactus.php">Contact Us</a></li>
                </ul>
            </div>
            <div class="footer-section social-media">
                <h3>SOCIAL MEDIA</h3>
                <ul>
                    <li><a href="https://www.facebook.com/kainanniatekabayan"><i class="fab fa-facebook-f"></i> Kainan ni Ate Kabayan</a></li>
                    <li><a href="https://www.instagram.com/kainanniatekabayan/"><i class="fab fa-instagram"></i> @Kainan ni Ate Kabayan</a></li>
                    <li><a href="https://share.google/I9ubNtooj7WKodVWl"><i class="fab fa-google"></i> Kainan ni Ate Kabayan</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom"><p>© 2026 Kainan ni Ate Kabayan. All Right Reserved.</p></div>
    </footer>   

    <script>
        const currentUserId = "<?php echo $user_id; ?>";
        const cartKey = 'cart_' + currentUserId;
    </script>
    <script src="menu.js"></script>
</body>
</html>