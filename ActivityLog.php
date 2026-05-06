<?php
include "db_conn.php"; 

// Kunin ang lahat ng logs mula sa database, pinakabago ang nasa itaas
$sql = "SELECT * FROM staff_activity_log ORDER BY timestamp DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log - Admin</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- External CSS -->
    <link rel="stylesheet" href="ActivityLog.css">
</head>
<body>
    <div class="container">
        <!-- SIDEBAR NAVBAR (Dashboard Style) -->
        <aside class="sidebar">
            <div class="logo-box">
                <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772298452/logo_ate_kabayan_jtfqeg.jpg" alt="Logo">
            </div>
            <nav>
                <a href="Dashboard.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'Dashboard.php') ? 'active' : ''; ?>"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
                <a href="MenuManagement.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'MenuManagement.php') ? 'active' : ''; ?>"><i class="fa-solid fa-utensils"></i> Menu Management</a>
                <a href="StaffActivity.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'StaffActivity.php') ? 'active' : ''; ?>"><i class="fa-solid fa-users"></i> Staff & Activity</a>

                <!-- BAGONG SEKSYON: Customer Management at Activity Log -->
                <a href="CustomerManagement.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'CustomerManagement.php') ? 'active' : ''; ?>"><i class="fa-solid fa-user-group"></i> Customer Management</a>
                <a href="ActivityLog.php" class="active"><i class="fa-solid fa-clock-rotate-left"></i> Activity Log</a>

                <!-- Existing Links Continued -->
                <a href="ServiceCenter.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'ServiceCenter.php') ? 'active' : ''; ?>"><i class="fa-solid fa-headset"></i> Service Center</a>
                <a href="Sales&Promotion.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'Sales&Promotion.php') ? 'active' : ''; ?>"><i class="fa-solid fa-tags"></i> Sales & Promotion</a>
                <a href="Settings.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'Settings.php') ? 'active' : ''; ?>"><i class="fa-solid fa-gear"></i> Settings</a>
            </nav>
        </aside>

        <!-- MAIN CONTENT -->
        <main>
            <!-- TOP HEADER -->
            <header>
                <div class="admin-title">
                    <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772298452/logo_ate_kabayan_jtfqeg.jpg" alt="Logo" class="mini-logo">
                    KAINAN NI ATE KABAYAN | <span>ADMIN</span>
                </div>
                <div class="header-icons">
                    <i class="fa-solid fa-comment-dots" id="msg-icon" title="Messages"></i>
                    <i class="fa-solid fa-bell" id="notif-icon" title="Notifications"></i>
                </div>
            </header>

            <!-- ACTIVITY LOG TABLE -->
            <section class="content-area">
                <h2 class="page-title">Activity Log</h2>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Staff Name</th>
                                <th>Action</th>
                                <th>Order ID</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($result && mysqli_num_rows($result) > 0) {
                                while($row = mysqli_fetch_assoc($result)) {
                                    $actionText = $row['action'];
                                    $iconClass = "fa-circle-info"; 
                                    
                                    if (strpos($actionText, 'Dispatched') !== false) $iconClass = "fa-truck-fast";
                                    if (strpos($actionText, 'Received') !== false) $iconClass = "fa-hand-holding-dollar";
                                    if (strpos($actionText, 'Created') !== false) $iconClass = "fa-user-plus";
                                    ?>
                                    <tr>
                                        <td><?php echo date('m/d/y H:i', strtotime($row['timestamp'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['staff_name']); ?></td>
                                        <td>
                                            <div class="action-cell">
                                                <i class="fa-solid <?php echo $iconClass; ?> action-icon"></i> 
                                                <?php echo htmlspecialchars($actionText); ?>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo "<tr><td colspan='4'>Walang nakitang activity log.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script src="ActivityLog.js"></script>
</body>
</html>