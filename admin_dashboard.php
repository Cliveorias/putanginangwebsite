<?php
session_start();
include "db_conn.php";

// Logic para sa Discount Approval
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $status = ($_GET['action'] == 'approve') ? 'Approved' : 'Rejected';
    $update_sql = "UPDATE user_discounts SET status = '$status' WHERE id = '$id'";
    mysqli_query($conn, $update_sql);
    header("Location: admin_dashboard.php?msg=status_updated");
    exit();
}

// 1. Fetch Pending Discounts
$discount_sql = "SELECT d.*, u.full_name FROM user_discounts d 
                 JOIN create_acc u ON d.user_id = u.id 
                 WHERE d.status = 'Pending'";
$discount_result = mysqli_query($conn, $discount_sql);

// 2. Fetch Today's Active Reservations (April 21, 2026 format base sa code mo)
$res_sql = "SELECT r.*, u.full_name FROM table_reservations r 
            JOIN create_acc u ON r.user_id = u.id 
            WHERE r.reservation_date = CURDATE() AND r.status != 'Cancelled'
            ORDER BY r.reservation_time ASC";
$res_result = mysqli_query($conn, $res_sql);

// 3. Fetch Customer Feedback Hub
$feed_pending_sql = "SELECT f.*, u.full_name FROM customer_feedback f 
                     JOIN create_acc u ON f.user_id = u.id 
                     WHERE f.status = 'Pending'";
$feed_pending_res = mysqli_query($conn, $feed_pending_sql);

$feed_published_sql = "SELECT f.*, u.full_name FROM customer_feedback f 
                       JOIN create_acc u ON f.user_id = u.id 
                       WHERE f.status = 'Published' ORDER BY f.created_at DESC LIMIT 4";
$feed_published_res = mysqli_query($conn, $feed_published_sql);

// 4. Fetch Contact Inquiries
$inq_sql = "SELECT * FROM contact_inquiries WHERE status = 'New' ORDER BY created_at DESC";
$inq_result = mysqli_query($conn, $inq_sql);
$inq_count = mysqli_num_rows($inq_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kainan ni Ate Kabayan | Admin Dashboard</title>
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Lightbox Modal Style para sa ID Preview */
        .img-modal {
            display: none; position: fixed; z-index: 9999;
            left: 0; top: 0; width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.9); justify-content: center; align-items: center;
        }
        .img-modal-content { max-width: 80%; max-height: 80%; border: 5px solid white; border-radius: 10px; }
        .id-thumb { width: 60px; height: 40px; object-fit: cover; border-radius: 4px; cursor: pointer; border: 1px solid #ddd; transition: 0.3s; }
        .id-thumb:hover { transform: scale(1.1); border-color: #FFB347; }
        .published-item { background: #f9f9f9; padding: 10px; border-radius: 8px; margin-bottom: 10px; border-left: 4px solid #FFB347; }
    </style>
</head>
<body>

    <header class="main-header">
        <div class="logo-section">
            <div class="logo-circle">
                <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772298452/logo_ate_kabayan_jtfqeg.jpg" alt="Logo"> 
            </div>
            <h1>KAINAN NI ATE KABAYAN <span>| ADMIN</span></h1>
        </div>
        <div class="header-icons">
            <span class="icon">💬</span>
            <span class="icon">🔔</span>
        </div>
    </header>

    <div class="admin-wrapper">
        <nav class="admin-sidebar">
            <div class="sidebar-title">NAVIGATION</div>
            <button class="side-btn active" onclick="showAdminSection('discount-section', this)">
                <span>🎟️</span> Discount Approvals
            </button>
            <button class="side-btn" onclick="showAdminSection('reservation-section', this)">
                <span>📅</span> Table Reservations
            </button>
            <button class="side-btn" onclick="showAdminSection('feedback-section', this)">
                <span>💬</span> Customer Feedback
            </button>
            <button class="side-btn" onclick="showAdminSection('inquiry-section', this)">
                <span>📥</span> Contact Inquiries
            </button>
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-link">🚪 Log Out</a>
            </div>
        </nav>

        <main class="admin-content">
            
            <section id="discount-section" class="admin-panel active">
                <div class="card">
                    <div class="card-header discount-header">
                        <h2>DISCOUNT APPROVAL HUB</h2>
                    </div>
                    <div class="card-content">
                        <h3>Pending Applications (Senior/PWD)</h3>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Type</th>
                                    <th>ID Front</th>
                                    <th>ID Back</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($discount_result) > 0): ?>
                                    <?php while($row = mysqli_fetch_assoc($discount_result)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                        <td><strong><?php echo $row['discount_type']; ?></strong></td>
                                        <td><img src="<?php echo $row['id_front']; ?>" class="id-thumb" onclick="viewFullImage(this.src)"></td>
                                        <td><img src="<?php echo $row['id_back']; ?>" class="id-thumb" onclick="viewFullImage(this.src)"></td>
                                        <td>
                                            <div style="display: flex; gap: 5px;">
                                                <a href="admin_dashboard.php?action=approve&id=<?php echo $row['id']; ?>" class="btn-action approve">Approve</a>
                                                <a href="admin_dashboard.php?action=reject&id=<?php echo $row['id']; ?>" class="btn-action reject">Reject</a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center">No pending applications.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="reservation-section" class="admin-panel">
                <div class="card">
                    <div class="card-header tracker-header">
                        <h2>TABLE RESERVATION TRACKER</h2>
                    </div>
                    <div class="card-content">
                        <h3>Today's Active Bookings (<?php echo date('F d, Y'); ?>)</h3>
                        <div class="filters">
                            <span class="badge pending">Pending Confirmation</span>
                            <span class="badge seated">Seated</span>
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr><th>Status</th><th>Time</th><th>Pax</th><th>Name</th></tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($res_result) > 0): ?>
                                    <?php while($res = mysqli_fetch_assoc($res_result)): 
                                        $status_class = (strtolower($res['status']) == 'seated') ? 'seated' : 'pending';
                                    ?>
                                    <tr>
                                        <td><span class="badge <?php echo $status_class; ?>"><?php echo $res['status']; ?></span></td>
                                        <td><?php echo date('h:i A', strtotime($res['reservation_time'])); ?></td>
                                        <td><?php echo $res['pax']; ?></td>
                                        <td><?php echo htmlspecialchars($res['full_name']); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center">No active bookings for today.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="feedback-section" class="admin-panel">
                <div class="card">
                    <div class="card-header feedback-header">
                        <h2>CUSTOMER FEEDBACK HUB</h2>
                    </div>
                    <div class="card-content">
                        <h3 class="tagline">MGA BUSOG NA NGITI!</h3>
                        <div class="feedback-queue">
                            <p class="section-label">Reviews Queue</p>
                            <?php if (mysqli_num_rows($feed_pending_res) > 0): ?>
                                <?php while($feed = mysqli_fetch_assoc($feed_pending_res)): ?>
                                    <div class="published-item">
                                        <strong><?php echo htmlspecialchars($feed['full_name']); ?></strong> (<?php echo $feed['rating']; ?>/5 Stars)
                                        <p style="font-size: 0.9rem; color: #555;"><?php echo htmlspecialchars($feed['comment']); ?></p>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="empty-msg">No pending reviews found.</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="recently-published" style="margin-top: 30px;">
                            <p class="section-label">RECENTLY PUBLISHED</p>
                            <div class="published-grid">
                                <?php while($pub = mysqli_fetch_assoc($feed_published_res)): ?>
                                    <div class="published-item" style="border-left-color: #27ae60;">
                                        <strong><?php echo htmlspecialchars($pub['full_name']); ?></strong>
                                        <p style="font-size: 0.85rem;"><?php echo htmlspecialchars($pub['comment']); ?></p>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="inquiry-section" class="admin-panel">
                <div class="card">
                    <div class="card-header inbox-header">
                        <h2>CONTACT INQUIRY INBOX</h2>
                    </div>
                    <div class="card-content">
                        <div class="inbox-top">
                            <h3>NEW MESSAGES</h3>
                            <span class="notif-badge"><?php echo $inq_count; ?> new</span>
                        </div>
                        <table class="inbox-table">
                            <thead>
                                <tr><th>Sender</th><th>Subject</th><th>Date</th></tr>
                            </thead>
                            <tbody>
                                <?php if ($inq_count > 0): ?>
                                    <?php while($inq = mysqli_fetch_assoc($inq_result)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($inq['sender_name']); ?></td>
                                        <td><?php echo htmlspecialchars($inq['subject']); ?></td>
                                        <td><?php echo date('m/d/y', strtotime($inq['created_at'])); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center">No new inquiries.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

        </main>
    </div>

    <div id="imageModal" class="img-modal" onclick="this.style.display='none'">
        <img class="img-modal-content" id="fullIDImage">
    </div>

    <script>
    function viewFullImage(src) {
        const modal = document.getElementById('imageModal');
        const fullImg = document.getElementById('fullIDImage');
        modal.style.display = "flex";
        fullImg.src = src;
    }

    function showAdminSection(sectionId, btn) {
        document.querySelectorAll('.admin-panel').forEach(panel => {
            panel.classList.remove('active');
        });
        document.querySelectorAll('.side-btn').forEach(b => {
            b.classList.remove('active');
        });
        document.getElementById(sectionId).classList.add('active');
        btn.classList.add('active');
    }
    </script>
</body>
</html>