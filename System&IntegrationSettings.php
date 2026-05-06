<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | Kainan ni Ate Kabayan</title>
    <link rel="stylesheet" href="Dashboard.css">
    <link rel="stylesheet" href="System&IntegrationsSettings.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="logo-box">
                <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772298452/logo_ate_kabayan_jtfqeg.jpg" alt="Logo">
            </div>
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

        <!-- Main Content -->
        <main>
            <header>
                <div class="admin-title">
                    <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772298452/logo_ate_kabayan_jtfqeg.jpg" alt="Logo" class="mini-logo">
                    KAINAN NI ATE KABAYAN | <span>ADMIN</span>
                </div>
                <div class="header-icons">
                    <i class="fa-solid fa-comment-dots"></i><i class="fa-solid fa-bell"></i><i class="fa-solid fa-bars"></i>
                </div>
            </header>

            <section class="content">
                <div class="settings-grid">
                    
                    <!-- LEFT COLUMN: STORE INFO -->
                    <div class="card settings-card animate-pop">
                        <div class="card-header-orange">
                            <i class="fa-solid fa-store"></i>
                            <h3>STORE INFORMATION MANAGEMENT</h3>
                        </div>
                        <div class="card-inner store-layout">
                            <div class="profile-section">
                                <h4>ADMIN PROFILE</h4>
                                <div class="profile-row">
                                    <div class="avatar-box"><i class="fa-solid fa-user"></i></div>
                                    <div class="profile-inputs">
                                        <div class="input-group-row">
                                            <input type="text" id="admin-name" value="Justin Tan">
                                            <span class="role-tag">Admin</span>
                                        </div>
                                        <input type="email" id="admin-email" value="justin_tan143@gmail.com">
                                        <div class="input-group-row">
                                            <input type="password" id="admin-pass" value="password123">
                                            <button class="btn-change">Change</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="hours-section">
                                <h4>OPERATING HOURS</h4>
                                <table>
                                    <thead><tr><th>DAY</th><th>OPEN TIME</th><th>CLOSE TIME</th></tr></thead>
                                    <tbody id="hours-body">
                                        <!-- Dynamic Days -->
                                    </tbody>
                                </table>
                            </div>

                            <div class="footer-info-section">
                                <h4>FOOTER INFO</h4>
                                <div class="footer-grid">
                                    <div class="desc-box">
                                        <label>Description</label>
                                        <textarea id="footer-desc"></textarea>
                                    </div>
                                    <div class="preview-box">
                                        <label>Live Preview</label>
                                        <div class="preview-img">
                                            <img src="https://via.placeholder.com/200x80" alt="Preview">
                                        </div>
                                    </div>
                                </div>
                                <button class="btn-save-all" onclick="saveStoreInfo()">Save All Store Info</button>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: DISCOUNT APPROVAL -->
                    <div class="card settings-card animate-pop" style="animation-delay: 0.2s;">
                        <div class="card-header-orange">
                            <i class="fa-solid fa-id-card"></i>
                            <h3>SENIOR/PWD DISCOUNT ID APPROVAL</h3>
                        </div>
                        <div class="card-inner">
                            <div class="approval-top">
                                <div class="status-indicators">
                                    <div class="status-box new"><span>NEW APPLICATIONS</span><strong>5 Pending</strong></div>
                                    <div class="status-box review"><span>PENDING REVIEW</span><strong>2 Flagged</strong></div>
                                </div>
                                <div class="queue-form">
                                    <h4>ID VERIFICATION QUEUE</h4>
                                    <div class="row">
                                        <input type="text" id="app-name" placeholder="Applicant Name">
                                        <select id="disc-type"><option>Senior Citizen</option><option>PWD</option></select>
                                    </div>
                                    <button class="btn-connect">Connect To Provider</button>
                                    <div class="notes-row">
                                        <input type="text" id="doc-notes" placeholder="Document Notes">
                                        <button class="btn-save-notes">Save Notes</button>
                                    </div>
                                </div>
                            </div>

                            <div class="dispatch-dashboard">
                                <h4>DISPATCH DASHBOARD</h4>
                                <div class="id-preview-box">
                                    <img src="https://via.placeholder.com/300x150" alt="ID Card">
                                </div>
                                <div class="queue-table">
                                    <table>
                                        <thead><tr><th>App ID</th><th>Status</th><th>ID Type</th><th>Actions</th></tr></thead>
                                        <tbody id="queue-body">
                                            <tr><td>90123</td><td><span class="status-pill green">Verified</span></td><td>Senior</td><td><i class="fa-solid fa-pen"></i> <i class="fa-solid fa-trash"></i></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="manual-approval">
                                <h4>MANUAL APPROVAL</h4>
                                <label>Application ID Number</label>
                                <input type="text" id="manual-id" placeholder="A12549631">
                                <label>ID Type</label>
                                <select><option>Senior Citizen</option><option>PWD</option></select>
                                <label>Final Review Notes</label>
                                <textarea id="final-notes"></textarea>
                                <div class="approval-btns">
                                    <button class="btn-approve" onclick="approveApp()">Approve</button>
                                    <button class="btn-decline" onclick="declineApp()">Decline</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </section>
        </main>
    </div>
    <script src="System&IntegrationsSettings.js"></script>
</body>
</html>