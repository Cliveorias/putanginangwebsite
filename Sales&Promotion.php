<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales & Promotion | Admin</title>
    <link rel="stylesheet" href="Dashboard.css">
    <link rel="stylesheet" href="Sales&Promotion.css">
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
                <a href="Dashboard.html"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
                <a href="MenuManagement.html"><i class="fa-solid fa-utensils"></i> Menu Management</a>
                <a href="StaffActivity.html"><i class="fa-solid fa-users"></i> Staff & Activity</a>
                <a href="ServiceCenter.html"><i class="fa-solid fa-headset"></i> Service Center</a>
                <a href="Sales&Promotion.html" class="active"><i class="fa-solid fa-tags"></i> Sales & Promotion</a>
                <a href="System&IntegrationsSettings.html"><i class="fa-solid fa-gear"></i> Settings</a>
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
                <div class="sales-layout-grid">
                    
                    <!-- LEFT COLUMN: SALES ANALYTICS -->
                    <div class="card sales-card animate-pop">
                        <div class="card-header-orange">
                            <i class="fa-solid fa-chart-line"></i>
                            <h3>SALES REPORTING & ANALYTICS</h3>
                        </div>
                        <div class="card-inner">
                            <div class="report-controls">
                                <div class="time-filters">
                                    <button class="filter-btn active" onclick="updateSalesTab('Daily')">Daily</button>
                                    <button class="filter-btn" onclick="updateSalesTab('Weekly')">Weekly</button>
                                    <button class="filter-btn" onclick="updateSalesTab('Monthly')">Monthly</button>
                                </div>
                                <div class="date-picker-box">
                                    <label>Date</label>
                                    <input type="date" id="report-date" value="2026-04-17">
                                </div>
                            </div>

                            <div class="stats-summary">
                                <div class="stat-item"><p>Total Earnings:</p><strong id="stat-earnings">₱12,450.00</strong></div>
                                <div class="stat-item"><p>Orders:</p><strong id="stat-orders">45</strong></div>
                                <div class="stat-item"><p>Average Check:</p><strong id="stat-avg">₱276</strong></div>
                            </div>

                            <div class="chart-section">
                                <h4 id="chart-title">DAILY EARNINGS</h4>
                                <div id="earnings-graph" class="svg-container"></div>
                            </div>

                            <!-- HIDDEN SECTION -->
                            <div id="insights-container" class="hidden-section">
                                <h4>TOP DISH INSIGHT</h4>
                                <div id="dish-insights-list"></div>
                            </div>

                            <button class="btn-dark-report" id="generate-report-btn">Generate Full Report</button>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: PROMO CONTROLS -->
                    <div class="card sales-card animate-pop" style="animation-delay: 0.2s;">
                        <div class="card-header-orange">
                            <i class="fa-solid fa-gift"></i>
                            <h3>VOUCHERS AND PROMO CONTROLS</h3>
                        </div>
                        <div class="card-inner">
                            <h4 class="section-label">ACTIVE & UPCOMING PROMOS</h4>
                            <div class="promo-table-container">
                                <table>
                                    <thead>
                                        <tr><th>Name</th><th>Code</th><th>Expiry</th><th>Status</th></tr>
                                    </thead>
                                    <tbody id="promo-list-body"></tbody>
                                </table>
                            </div>
                            <button class="btn-green-promo">+ Create New Promo Code</button>

                            <div class="promo-form">
                                <div class="form-group">
                                    <label>Promo Name</label>
                                    <input type="text" id="p-name" placeholder="Enter promo name">
                                </div>
                                <div class="form-group">
                                    <label>Code</label>
                                    <input type="text" id="p-code" placeholder="Enter code">
                                </div>
                                <div class="row-form">
                                    <div class="form-group">
                                        <label>Discount</label>
                                        <input type="text" id="p-discount" placeholder="%">
                                    </div>
                                    <div class="form-group">
                                        <label>Expiry</label>
                                        <input type="date" id="p-expiry" value="2026-04-17">
                                    </div>
                                </div>
                                <button class="btn-orange-add" onclick="addNewPromo()">Add Promo</button>
                            </div>
                        </div>
                    </div>

                </div>
            </section>
        </main>
    </div>
    <script src="Sales&Promotion.js"></script>
</body>
</html>