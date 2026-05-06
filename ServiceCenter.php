<?php
include "db_conn.php"; // Connect sa database

// --- PROCESS ADMIN ACTIONS PARA SA RESERVATION (Walang bago dito) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['res_id'])) {
    $action = $_POST['action'];
    $res_id = mysqli_real_escape_string($conn, $_POST['res_id']);
    
    if ($action === 'accept') {
        $update_sql = "UPDATE table_reservations SET status = 'Seated' WHERE id = '$res_id'";
    } elseif ($action === 'decline') {
        $update_sql = "UPDATE table_reservations SET status = 'Cancelled' WHERE id = '$res_id'";
    } elseif ($action === 'accept_cancel') {
        $update_sql = "UPDATE table_reservations SET status = 'Cancelled' WHERE id = '$res_id'";
    } elseif ($action === 'reject_cancel') {
        $update_sql = "UPDATE table_reservations SET status = 'Active (Cancel Denied)' WHERE id = '$res_id'";
    }
    
    if(isset($update_sql)) {
        mysqli_query($conn, $update_sql);
        header("Location: ServiceCenter.php");
        exit();
    }
}

// Kunin ang reservations
$res_sql = "SELECT r.*, c.full_name as account_name 
            FROM table_reservations r 
            LEFT JOIN create_acc c ON r.user_id = c.id 
            ORDER BY r.reservation_date ASC, r.reservation_time ASC";
$res_result = mysqli_query($conn, $res_sql);

// BAGO: Kunin ang listahan ng mga customers na may messages para sa Chat List
$chat_users_sql = "SELECT DISTINCT c.id, c.full_name, c.profile_pic 
                   FROM create_acc c 
                   JOIN chat_messages m ON c.id = m.user_id 
                   ORDER BY m.created_at DESC";
$chat_users_result = mysqli_query($conn, $chat_users_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Center | Kainan ni Ate Kabayan</title>
    <link rel="stylesheet" href="Dashboard.css">
    <link rel="stylesheet" href="ServiceCenter.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        .service-grid {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important; 
            gap: 30px !important;
            min-height: calc(100vh - 130px); 
            align-items: stretch !important;
        }
        .service-card {
            display: flex !important;
            flex-direction: column !important;
            height: 100% !important; 
        }
        .card-inner {
            flex-grow: 1 !important;
            display: flex !important;
            flex-direction: column !important;
        }
        .table-list-container {
            flex-grow: 1 !important; 
            min-height: 400px; 
            overflow-y: auto;
        }
        .status-Pending-Confirmation { color: #F4A42B; font-weight: 700; }
        .status-Seated { color: #38b000; font-weight: 700; }
        .status-Cancelled { color: #dc3545; font-weight: 700; }
        .status-Waiting-for-Approval { color: #17a2b8; font-weight: 700; }
        .status-Active-\(Cancel-Denied\) { color: #38b000; font-weight: 700; }
        
        .res-action-form { display: inline-block; margin: 0; }
        .btn-res-action { border: none; padding: 5px 10px; border-radius: 50px; color: white; cursor: pointer; font-size: 0.75rem; font-weight: 600; margin-right: 2px; }
        .btn-accept { background: #28a745; }
        .btn-decline { background: #dc3545; }

        /* BAGO: Messenger Admin Chat Styles */
        .admin-chat-layout {
            display: flex;
            height: 100%;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e4e6eb;
        }
        /* Left: User List */
        .chat-sidebar {
            width: 35%;
            border-right: 1px solid #e4e6eb;
            display: flex;
            flex-direction: column;
        }
        .sidebar-header { padding: 15px; border-bottom: 1px solid #e4e6eb; }
        .sidebar-header h4 { margin: 0; font-size: 1.2rem; }
        .user-list { flex: 1; overflow-y: auto; }
        .user-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            gap: 12px;
            cursor: pointer;
            transition: 0.2s;
        }
        .user-item:hover { background: #f2f3f5; }
        .user-item.active { background: #e7f3ff; }
        .user-avatar-small { width: 45px; height: 45px; border-radius: 50%; overflow: hidden; background: #ddd; }
        .user-avatar-small img { width: 100%; height: 100%; object-fit: cover; }
        .user-info-brief h5 { margin: 0; font-size: 0.95rem; color: #050505; }

        /* Right: Chat Window */
        .chat-main { width: 65%; display: flex; flex-direction: column; background: #fff; }
        .chat-main-header {
            padding: 10px 20px;
            border-bottom: 1px solid #e4e6eb;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .chat-area { flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 5px; }
        .admin-input-area { padding: 15px; border-top: 1px solid #e4e6eb; display: flex; align-items: center; }
        .admin-input-area input {
            flex: 1; padding: 10px 15px; background: #f0f2f5; border: none; border-radius: 20px; outline: none;
        }
        .btn-admin-send { background: none; border: none; color: #F4A42B; font-size: 1.2rem; margin-left: 10px; cursor: pointer; }
        
        /* Empty State */
        .chat-empty { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #65676b; }
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="logo-box">
                <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772298452/logo_ate_kabayan_jtfqeg.jpg" alt="Logo">
            </div>
            <nav>
                <a href="Dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
                <a href="MenuManagement.php"><i class="fa-solid fa-utensils"></i> Menu Management</a>
                <a href="StaffActivity.php"><i class="fa-solid fa-users"></i> Staff & Activity</a>
                <a href="CustomerManagement.php"><i class="fa-solid fa-user-group"></i> Customer Management</a>
                <a href="ActivityLog.php"><i class="fa-solid fa-clock-rotate-left"></i> Activity Log</a>
                <a href="ServiceCenter.php" class="active"><i class="fa-solid fa-headset"></i> Service Center</a>
                <a href="Sales&Promotion.php"><i class="fa-solid fa-tags"></i> Sales & Promotion</a>
                <a href="Settings.php"><i class="fa-solid fa-gear"></i> Settings</a>
            </nav>
        </aside>

        <main>
            <header>
                <div class="admin-title">
                    <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772298452/logo_ate_kabayan_jtfqeg.jpg" alt="Logo" class="mini-logo">
                    KAINAN NI ATE KABAYAN | <span>ADMIN</span>
                </div>
                <div class="header-icons"><i class="fa-solid fa-comment-dots"></i><i class="fa-solid fa-bell"></i><i class="fa-solid fa-bars"></i></div>
            </header>

            <section class="content">
                <div class="service-grid">
                    
                    <!-- TABLE RESERVATION TRACKER (Walang bago dito) -->
                    <div class="card service-card animate-pop">
                        <div class="card-header-orange"><i class="fa-solid fa-calendar-days"></i><h3>TABLE RESERVATION TRACKER</h3></div>
                        <div class="card-inner">
                            <div class="quick-filters" style="display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 10px;">
                                <button class="filter-pill pending" onclick="filterReservations('Pending Confirmation')">Pending</button>
                                <button class="filter-pill seated" onclick="filterReservations('Seated')">Seated</button>
                                <button class="filter-pill all" onclick="filterReservations('All')">All</button>
                            </div>
                            <div class="table-list-container">
                                <table>
                                    <thead><tr><th>Status & Action</th><th>Time/Date</th><th>Pax</th><th>Name</th></tr></thead>
                                    <tbody id="reservation-list">
                                        <?php 
                                        if ($res_result && mysqli_num_rows($res_result) > 0) {
                                            while($row = mysqli_fetch_assoc($res_result)) {
                                                $res_id = $row['id'];
                                                $status = htmlspecialchars($row['status']);
                                                $name = htmlspecialchars($row['name']); 
                                                $acc_name = htmlspecialchars($row['account_name'] ?? 'Guest');
                                                $date = date('M d, Y', strtotime($row['reservation_date']));
                                                $time = date('h:i A', strtotime($row['reservation_time']));
                                                $status_class = "status-" . str_replace([' ', '(', ')'], ['-', '', ''], $status);
                                                echo "<tr class='res-row' data-status='$status'>";
                                                echo "<td><span class='$status_class'>$status</span><div class='action-cell' style='margin-top:5px;'>";
                                                if ($status == 'Pending Confirmation') {
                                                    echo "<form method='POST' class='res-action-form'><input type='hidden' name='res_id' value='$res_id'><input type='hidden' name='action' value='accept'><button type='submit' class='btn-res-action btn-accept'>Accept</button></form>";
                                                    echo "<form method='POST' class='res-action-form'><input type='hidden' name='res_id' value='$res_id'><input type='hidden' name='action' value='decline'><button type='submit' class='btn-res-action btn-decline'>Decline</button></form>";
                                                }
                                                echo "</div></td><td>$date<br><small>$time</small></td><td>{$row['pax']}</td><td><strong>$name</strong><br><small>Acc: $acc_name</small></td></tr>";
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- BAGO: Messenger-style Admin Chat Interface -->
                    <div class="card service-card animate-pop">
                        <div class="card-inner" style="padding: 0;">
                            <div class="admin-chat-layout">
                                <!-- Chat List -->
                                <div class="chat-sidebar">
                                    <div class="sidebar-header"><h4>Chats</h4></div>
                                    <div class="user-list">
                                        <?php while($user = mysqli_fetch_assoc($chat_users_result)): ?>
                                        <div class="user-item" onclick="selectUserForChat(<?php echo $user['id']; ?>, '<?php echo addslashes($user['full_name']); ?>', '<?php echo $user['profile_pic']; ?>', this)">
                                            <div class="user-avatar-small">
                                                <img src="<?php echo !empty($user['profile_pic']) ? $user['profile_pic'] : 'https://www.w3schools.com/howto/img_avatar.png'; ?>">
                                            </div>
                                            <div class="user-info-brief"><h5><?php echo $user['full_name']; ?></h5></div>
                                        </div>
                                        <?php endwhile; ?>
                                    </div>
                                </div>

                                <!-- Chat Window -->
                                <div class="chat-main" id="chat-main-window">
                                    <div class="chat-empty" id="chat-empty-state">
                                        <i class="fa-brands fa-facebook-messenger" style="font-size: 3rem; margin-bottom: 10px; color: #ddd;"></i>
                                        <p>Pili ka ng ka-chat, Kabayan!</p>
                                    </div>
                                    
                                    <div id="active-chat-container" style="display: none; flex-direction: column; height: 100%;">
                                        <div class="chat-main-header">
                                            <div class="user-avatar-small" id="header-user-img-container">
                                                <img id="header-user-img" src="">
                                            </div>
                                            <div class="user-info-brief"><h5 id="header-user-name">Name</h5></div>
                                        </div>
                                        <div class="chat-area" id="admin-chat-box"></div>
                                        <div class="admin-input-area">
                                            <input type="text" id="admin-chat-input" placeholder="Aa">
                                            <button class="btn-admin-send" onclick="sendAdminChatMessage()"><i class="fa-solid fa-paper-plane"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </section>
        </main>
    </div>

    <script>
        let selectedCustomerId = null;

        function selectUserForChat(id, name, pic, element) {
            selectedCustomerId = id;
            document.querySelectorAll('.user-item').forEach(i => i.classList.remove('active'));
            element.classList.add('active');
            
            document.getElementById('chat-empty-state').style.display = 'none';
            document.getElementById('active-chat-container').style.display = 'flex';
            document.getElementById('header-user-name').innerText = name;
            document.getElementById('header-user-img').src = pic ? pic : 'https://www.w3schools.com/howto/img_avatar.png';
            
            fetchAdminMessages();
        }

        function fetchAdminMessages() {
            if (selectedCustomerId) {
                fetch('fetch_messages.php?customer_id=' + selectedCustomerId)
                .then(res => res.text())
                .then(data => {
                    const box = document.getElementById('admin-chat-box');
                    let isAtBottom = box.scrollHeight - box.scrollTop <= box.clientHeight + 50;
                    box.innerHTML = data;
                    if (isAtBottom) box.scrollTop = box.scrollHeight;
                });
            }
        }

        setInterval(fetchAdminMessages, 2000);

        function sendAdminChatMessage() {
            const input = document.getElementById('admin-chat-input');
            const msg = input.value.trim();
            if(!msg || !selectedCustomerId) return;

            const formData = new FormData();
            formData.append('message', msg);
            formData.append('sender_type', 'Admin');
            formData.append('customer_id', selectedCustomerId);

            fetch('send_message.php', { method: 'POST', body: formData })
            .then(res => res.text())
            .then(data => {
                if(data.trim() === "success") {
                    input.value = "";
                    fetchAdminMessages();
                }
            });
        }

        document.getElementById('admin-chat-input').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendAdminChatMessage();
        });

        function filterReservations(status) {
            document.querySelectorAll('.res-row').forEach(row => {
                row.style.display = (status === 'All' || row.getAttribute('data-status') === status) ? '' : 'none';
            });
        }
    </script>
</body>
</html>