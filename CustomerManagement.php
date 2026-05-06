<?php
session_start();
include "db_conn.php"; 

// Helper function para sa "Active" status logic
function time_ago($timestamp) {
    if(empty($timestamp)) return "Never logged in";
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    
    $minutes = round($seconds / 60);
    $hours   = round($seconds / 3600);
    $days    = round($seconds / 86400);

    if($seconds <= 60) return "Just now";
    else if($minutes <= 60) return ($minutes == 1) ? "1 min ago" : "$minutes mins ago";
    else if($hours <= 24) return ($hours == 1) ? "1 hr ago" : "$hours hrs ago";
    else return date("M d, Y", $time_ago);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="CustomerManagement.css">
</head>
<body>
    <!-- Sidebar Design -->
    <aside class="sidebar">
        <div class="logo-box">
            <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772298452/logo_ate_kabayan_jtfqeg.jpg" alt="Logo">
        </div>
        <ul class="nav-links">
            <li><a href="Dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="MenuManagement.php"><i class="fa-solid fa-utensils"></i> Menu Management</a></li>
            <li><a href="StaffActivity.php"><i class="fa-solid fa-users"></i> Staff & Activity</a></li>
            <li class="active"><a href="CustomerManagement.php"><i class="fa-solid fa-user-group"></i> Customer Management</a></li>
            <li><a href="ActivityLog.php"><i class="fa-solid fa-clock-rotate-left"></i> Activity Log</a></li>
            <li><a href="ServiceCenter.php"><i class="fa-solid fa-headset"></i> Service Center</a></li>
            <li><a href="Settings.php"><i class="fa-solid fa-gear"></i> Settings</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <header>
            <div class="header-title">KAINAN NI ATE KABAYAN <span>ADMIN</span></div>
            <div class="header-icons"><i class="fa-solid fa-comment-dots"></i><i class="fa-solid fa-bell"></i></div>
        </header>

        <main class="container">
            <h2 class="page-title">Customer Management</h2>
            <div class="customer-list">
                <?php
                // Pagkuha ng mga registered customers
                $sql = "SELECT * FROM create_acc WHERE role = 'Customer' ORDER BY full_name ASC";
                $result = mysqli_query($conn, $sql);

                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $c_id = $row['id'];
                        
                        // 1. DYNAMIC ORDER COUNT: Bilangin ang lahat ng orders na Completed o Paid
                        $order_count_res = mysqli_query($conn, "SELECT COUNT(*) as total_orders FROM orders WHERE user_id = '$c_id' AND (status = 'Completed' OR status = 'Paid')");
                        $order_data = mysqli_fetch_assoc($order_count_res);
                        $total_orders = $order_data['total_orders'] ?? 0;

                        // 2. DYNAMIC TOTAL SPENT: Sum ng total_price para sa Completed/Paid orders lang
                        $spent_res = mysqli_query($conn, "SELECT SUM(total_price) as total_spent FROM orders WHERE user_id = '$c_id' AND (status = 'Completed' OR status = 'Paid')"); 
                        $spent_data = mysqli_fetch_assoc($spent_res);
                        $total_spent = number_format($spent_data['total_spent'] ?? 0, 0);

                        $profile_pic = !empty($row['profile_pic']) ? $row['profile_pic'] : "";
                        
                        // 3. REGISTRATION & ACTIVITY LOGIC
                        $reg_date = isset($row['created_at']) ? date("M d, Y", strtotime($row['created_at'])) : "N/A";
                        $active_status = time_ago($row['last_login'] ?? '');
                        ?>
                        <div class="customer-card">
                            <div class="card-accent"></div>
                            <div class="card-body">
                                <div class="customer-info">
                                    <div class="avatar">
                                        <?php if ($profile_pic): ?>
                                            <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Avatar">
                                        <?php else: ?>
                                            <i class="fa-solid fa-user"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="details">
                                        <h3><?php echo htmlspecialchars($row['full_name']); ?></h3>
                                        <div class="contact-row">
                                            <?php if(!empty($row['email'])): ?>
                                                <span><i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($row['email']); ?></span>
                                            <?php endif; ?>
                                            <span><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($row['contact_number'] ?? 'N/A'); ?></span>
                                            
                                            <!-- Dagdag na info para sa Registration at Activity -->
                                            <span class="meta-info"><i class="fa-solid fa-calendar-days"></i> Registered: <?php echo $reg_date; ?></span>
                                            <span class="status-indicator">
                                                <span class="dot <?php echo (strpos($active_status, 'min') !== false || $active_status == 'Just now') ? 'online' : ''; ?>"></span>
                                                Active: <?php echo $active_status; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="stats-row">
                                    <div class="stat"><i class="fa-solid fa-bag-shopping"></i> Orders: <strong><?php echo $total_orders; ?></strong></div>
                                    <div class="stat"><i class="fa-solid fa-wallet"></i> Total: <strong>₱<?php echo $total_spent; ?></strong></div>
                                </div>

                                <div class="actions">
                                    <button class="btn-action btn-block" onclick="blockUser(<?php echo $row['id']; ?>)">
                                        <i class="fa-solid fa-user-slash"></i> Block
                                    </button>
                                    <button class="btn-action btn-delete" onclick="deleteUser(<?php echo $row['id']; ?>)">
                                        <i class="fa-solid fa-trash-can"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </main>
    </div>
    <script src="CustomerManagement.js"></script>
</body>
</html>