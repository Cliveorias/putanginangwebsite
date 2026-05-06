<?php
session_start();
include "db_conn.php"; // Siniguradong connected sa database para makuha ang staff list
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Operations | Admin</title>
    <link rel="stylesheet" href="Dashboard.css">
    <link rel="stylesheet" href="StaffActivity.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Dagdag na style para sa status badges sa table[cite: 9] */
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
        }
        .status-on { background: #d4edda; color: #155724; } /* Green para sa On Duty */
        .status-off { background: #f8d7da; color: #721c24; } /* Red para sa Off Duty */
        
        .btn-delete {
            color: #e74c3c;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-delete:hover {
            transform: scale(1.2);
            color: #c0392b;
        }

        /* FIX PARA SA CONSISTENCY NG INPUT FIELDS[cite: 9] */
        .input-wrapper {
            background: #dcdcdc !important; /* Grey background gaya ng nasa screenshot mo */
            border-radius: 12px !important;
            padding: 5px 15px !important;
            display: flex;
            align-items: center;
            gap: 10px;
            border: none !important;
        }
        .input-wrapper input, .input-wrapper select {
            background: transparent !important;
            border: none !important;
            outline: none !important;
            width: 100% !important;
            height: 40px !important;
            color: #333 !important;
            font-family: 'Poppins', sans-serif !important;
        }
        .input-wrapper i {
            color: #666 !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="logo-box">
                <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772298452/logo_ate_kabayan_jtfqeg.jpg" alt="Logo">
            </div>
            <nav>
                <a href="Dashboard.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'Dashboard.php') ? 'active' : ''; ?>"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
                <a href="MenuManagement.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'MenuManagement.php') ? 'active' : ''; ?>"><i class="fa-solid fa-utensils"></i> Menu Management</a>
                <a href="StaffActivity.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'StaffActivity.php') ? 'active' : ''; ?>"><i class="fa-solid fa-users"></i> Staff & Activity</a>
                <a href="CustomerManagement.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'CustomerManagement.php') ? 'active' : ''; ?>"><i class="fa-solid fa-user-group"></i> Customer Management</a>
                <a href="ActivityLog.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'ActivityLog.php') ? 'active' : ''; ?>"><i class="fa-solid fa-clock-rotate-left"></i> Activity Log</a>
                <a href="ServiceCenter.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'ServiceCenter.php') ? 'active' : ''; ?>"><i class="fa-solid fa-headset"></i> Service Center</a>
                <a href="Sales&Promotion.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'Sales&Promotion.php') ? 'active' : ''; ?>"><i class="fa-solid fa-tags"></i> Sales & Promotion</a>
                <a href="Settings.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'Settings.php') ? 'active' : ''; ?>"><i class="fa-solid fa-gear"></i> Settings</a>
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
                <h2 class="page-main-title">Staff Operations</h2>

                <div class="staff-layout-grid">
                    <div class="left-col">
                        <div class="card staff-card animate-pop" style="animation-delay: 0.2s;">
                            <div class="card-header-orange">
                                <h3>STAFF DIRECTORY</h3>
                            </div>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Staff Name</th>
                                            <th>Role</th>
                                            <th>Contact Number</th>
                                            <th>Status</th>
                                            <th style="text-align: center;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="staff-directory-body">
                                        <?php
                                        // Kukunin ang lahat ng staff
                                        $sql_staff = "SELECT * FROM staff ORDER BY name ASC";
                                        $res_staff = mysqli_query($conn, $sql_staff);
                                        
                                        if($res_staff && mysqli_num_rows($res_staff) > 0) {
                                            while($staff = mysqli_fetch_assoc($res_staff)) {
                                                $s_name = htmlspecialchars($staff['name']);
                                                $s_role = htmlspecialchars($staff['role']);
                                                $s_contact = htmlspecialchars($staff['contact']);
                                                $s_status = htmlspecialchars($staff['status']);
                                                
                                                $status_class = (strtolower($s_status) == 'on duty') ? 'status-on' : 'status-off';
                                                
                                                echo "<tr>
                                                        <td><strong>$s_name</strong></td>
                                                        <td>$s_role</td>
                                                        <td>$s_contact</td>
                                                        <td><span class='status-badge $status_class'>$s_status</span></td>
                                                        <td style='text-align: center;'>
                                                            <i class='fa-solid fa-trash btn-delete' onclick='deleteStaff({$staff['id']})'></i>
                                                        </td>
                                                      </tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='5' style='text-align:center; color:#888;'>No staff members found.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="right-col">
                        <div class="card staff-card form-card animate-pop" style="animation-delay: 0.4s;">
                            <div class="card-header-orange">
                                <h3>CREATE STAFF ACCOUNT</h3>
                            </div>
                            <div class="form-content">
                                <!-- FULL NAME[cite: 9] -->
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <div class="input-wrapper">
                                        <input type="text" id="staff-name" placeholder="Enter full name">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                </div>
                                <!-- ROLE[cite: 9] -->
                                <div class="form-group">
                                    <label>Role</label>
                                    <div class="input-wrapper">
                                        <select class="staff-select" id="staff-role">
                                            <option value="" disabled selected>Select Role</option>
                                            <option value="Head Chef">Head Chef</option>
                                            <option value="Kitchen Staff">Kitchen Staff</option>
                                            <option value="Delivery Rider">Delivery Rider</option>
                                            <option value="Admin Assistant">Admin Assistant</option>
                                        </select>
                                    </div>
                                </div>
                                <!-- EMAIL ADDRESS[cite: 9] -->
                                <div class="form-group">
                                    <label>Email Address</label>
                                    <div class="input-wrapper">
                                        <input type="email" id="staff-email" placeholder="example@email.com">
                                        <i class="fa-solid fa-envelope"></i>
                                    </div>
                                </div>
                                <!-- CONTACT NUMBER[cite: 9] -->
                                <div class="form-group">
                                    <label>Contact Number</label>
                                    <div class="input-wrapper">
                                        <input type="text" id="staff-contact" placeholder="09xxxxxxxxx">
                                        <i class="fa-solid fa-phone"></i>
                                    </div>
                                </div>
                                <!-- PASSWORD[cite: 9] -->
                                <div class="form-group">
                                    <label>Password</label>
                                    <div class="input-wrapper">
                                        <input type="password" id="staff-password" placeholder="Create password">
                                        <i class="fa-solid fa-lock"></i>
                                    </div>
                                </div>
                                <!-- CONFIRM PASSWORD[cite: 9] -->
                                <div class="form-group">
                                    <label>Confirm Password</label>
                                    <div class="input-wrapper">
                                        <input type="password" id="staff-confirm-password" placeholder="Repeat password">
                                        <i class="fa-solid fa-shield-halved"></i>
                                    </div>
                                </div>
                                <button class="btn-orange-add" id="add-staff-btn">Register Staff Account</button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <script src="StaffActivity.js"></script>
</body>
</html>