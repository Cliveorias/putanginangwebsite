<?php
include "db_conn.php"; // Koneksyon sa database[cite: 1]

// --- AJAX HANDLER (Para sa Real-time at Filtered Updates) ---
if (isset($_GET['ajax'])) {
    $range = $_GET['range'] ?? 'all'; // Kunin ang napiling filter
    
    // 1. Today's Total Order
    $today_orders_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE DATE(created_at) = CURDATE()");
    $today_orders = mysqli_fetch_assoc($today_orders_query)['total'] ?? 0;

    // 2. Gross Revenue (May Filter Logic na base sa benta)
    $rev_where = "WHERE status = 'Completed'";
    if ($range == 'today') {
        $rev_where .= " AND DATE(created_at) = CURDATE()";
    } elseif ($range == 'weekly') {
        $rev_where .= " AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    } elseif ($range == 'monthly') {
        $rev_where .= " AND created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
    } elseif ($range == 'yearly') {
        $rev_where .= " AND created_at >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
    }
    
    $revenue_query = mysqli_query($conn, "SELECT SUM(total_price) as total FROM orders $rev_where");
    $gross_revenue = mysqli_fetch_assoc($revenue_query)['total'] ?? 0;

    // 3. Customers
    $customers_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM create_acc");
    $total_customers = mysqli_fetch_assoc($customers_query)['total'] ?? 0;

    // 4. Average Order Value
    $aov_query = mysqli_query($conn, "SELECT AVG(total_price) as total FROM orders");
    $avg_order_value = mysqli_fetch_assoc($aov_query)['total'] ?? 0;

    echo json_encode([
        'today_orders' => $today_orders,
        'gross_revenue' => '₱' . number_format($gross_revenue, 2),
        'total_customers' => $total_customers,
        'avg_order_value' => '₱' . number_format($avg_order_value, 2)
    ]);
    exit;
}

// --- INITIAL LOAD QUERIES ---
$today_orders_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE DATE(created_at) = CURDATE()");
$today_orders = mysqli_fetch_assoc($today_orders_query)['total'] ?? 0;

$revenue_query = mysqli_query($conn, "SELECT SUM(total_price) as total FROM orders WHERE status = 'Completed'");
$gross_revenue = mysqli_fetch_assoc($revenue_query)['total'] ?? 0;

$customers_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM create_acc");
$total_customers = mysqli_fetch_assoc($customers_query)['total'] ?? 0;

$aov_query = mysqli_query($conn, "SELECT AVG(total_price) as total FROM orders");
$avg_order_value = mysqli_fetch_assoc($aov_query)['total'] ?? 0;

$best_sellers_query = mysqli_query($conn, "
    SELECT oi.food_name, oi.price, mi.image_url, COUNT(*) as sales_count 
    FROM order_items oi
    LEFT JOIN menu_items mi ON oi.food_name = mi.name 
    GROUP BY oi.food_name 
    ORDER BY sales_count DESC 
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Kainan ni Ate Kabayan</title>
    <link rel="stylesheet" href="Dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="logo-box">
                <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772298452/logo_ate_kabayan_jtfqeg.jpg" alt="Logo">
            </div>
            <nav>
    <nav>
        <!-- Existing Links -->
        <a href="Dashboard.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'Dashboard.php') ? 'active' : ''; ?>"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
        <a href="MenuManagement.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'MenuManagement.php') ? 'active' : ''; ?>"><i class="fa-solid fa-utensils"></i> Menu Management</a>
        <a href="StaffActivity.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'StaffActivity.php') ? 'active' : ''; ?>"><i class="fa-solid fa-users"></i> Staff & Activity</a>

        <!-- BAGONG SEKSYON: Customer Management at Activity Log -->
        <a href="CustomerManagement.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'CustomerManagement.php') ? 'active' : ''; ?>"><i class="fa-solid fa-user-group"></i> Customer Management</a>
        <a href="ActivityLog.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'ActivityLog.php') ? 'active' : ''; ?>"><i class="fa-solid fa-clock-rotate-left"></i> Activity Log</a>

        <!-- Existing Links Continued -->
        <a href="ServiceCenter.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'ServiceCenter.php') ? 'active' : ''; ?>"><i class="fa-solid fa-headset"></i> Service Center</a>
        <a href="Sales&Promotion.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'Sales&Promotion.php') ? 'active' : ''; ?>"><i class="fa-solid fa-tags"></i> Sales & Promotion</a>
        <a href="Settings.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'Settings.php') ? 'active' : ''; ?>"><i class="fa-solid fa-gear"></i> Settings</a>
    </nav>
</aside>
            </nav>
        </aside>

        <main>
            <header>
                <div class="admin-title">
                    <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772298452/logo_ate_kabayan_jtfqeg.jpg" alt="Logo" class="mini-logo">
                    KAINAN NI ATE KABAYAN | <span>ADMIN</span>
                </div>
                <div class="header-icons">
                    <i class="fa-solid fa-comment-dots"></i>
                    <i class="fa-solid fa-bell"></i>
                    <i class="fa-solid fa-bars"></i>
                </div>
            </header>

            <section class="content">
                <div class="stat-grid">
                    <div class="card stat-card">
                        <div class="card-info">
                            <h4>Today's Total Order</h4>
                            <h2 id="total-orders-val"><?php echo $today_orders; ?></h2>
                        </div>
                        <div class="card-graph" id="graph-orders"></div>
                    </div>
                    
                    <!-- DITO NATIN NILAGAY ANG FILTER PARA SA REVENUE[cite: 7] -->
                    <div class="card stat-card">
                        <div class="card-info">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <h4>Gross Revenue</h4>
                                <select id="revenue-range" onchange="updateDashboardStats()" style="border: none; background: transparent; font-size: 0.7rem; font-family: 'Poppins'; cursor: pointer; outline: none; color: #666;">
                                    <option value="all">All Time</option>
                                    <option value="today">Today</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>
                            <h2 id="gross-revenue-val">₱<?php echo number_format($gross_revenue, 2); ?></h2>
                        </div>
                        <div class="card-graph" id="graph-revenue"></div>
                    </div>

                    <div class="card stat-card">
                        <div class="card-info">
                            <h4>Customers</h4>
                            <h2 id="total-customers-val"><?php echo $total_customers; ?></h2>
                        </div>
                        <div class="card-graph" id="graph-customers"></div>
                    </div>
                    <div class="card stat-card">
                        <div class="card-info">
                            <h4>Average Order Value</h4>
                            <h2 id="avg-value-val">₱<?php echo number_format($avg_order_value, 2); ?></h2>
                        </div>
                        <div class="card-graph" id="graph-value"></div>
                    </div>
                </div>

                <div class="bottom-grid">
                    <div class="card main-chart-box">
                        <h3>Daily Sales Trend</h3>
                        <div class="pie-container">
                            <div class="pie-chart" id="pie-chart"></div>
                            <div class="pie-legend">
                                <div><span class="dot special"></span> Special Goto</div>
                                <div><span class="dot silog"></span> Silog Meals</div>
                                <div><span class="dot sizzling"></span> Sizzling</div>
                            </div>
                        </div>
                    </div>

                    <div class="card best-selling-box">
                        <h3>Top 5 Best-Selling Dishes</h3>
                        <div class="dish-list">
                            <?php 
                            if(mysqli_num_rows($best_sellers_query) > 0) {
                                while($row = mysqli_fetch_assoc($best_sellers_query)) { 
                                    $food_pic = !empty($row['image_url']) ? $row['image_url'] : 'https://res.cloudinary.com/dn38jxbeh/image/upload/v1773501436/Tokwat_Lechon_l69imm.png';
                            ?>
                                <div class="dish-item">
                                    <img src="<?php echo $food_pic; ?>" alt="<?php echo $row['food_name']; ?>">
                                    <div class="dish-info">
                                        <h4><?php echo $row['food_name']; ?></h4> 
                                        <p>₱<?php echo number_format($row['price'], 2); ?></p>
                                    </div>
                                </div>
                            <?php 
                                } 
                            } else {
                                echo "<p style='padding: 20px; font-size: 0.9rem;'>Wala pang data ng benta, Kabayan!</p>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        function updateDashboardStats() {
            // Kunin ang value ng napiling filter
            const range = document.getElementById('revenue-range').value;
            
            // Ipasa ang 'range' sa fetch URL
            fetch(`Dashboard.php?ajax=1&range=${range}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('total-orders-val').innerText = data.today_orders;
                    document.getElementById('gross-revenue-val').innerText = data.gross_revenue;
                    document.getElementById('total-customers-val').innerText = data.total_customers;
                    document.getElementById('avg-value-val').innerText = data.avg_order_value;
                })
                .catch(error => console.error('Error:', error));
        }

        // Kusa pa ring mag-uupdate bawat 10 segundo para sa ibang stats
        setInterval(updateDashboardStats, 10000);
    </script>
    <script src="Dashboard.js"></script>
</body>
</html>