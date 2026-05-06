<?php
session_start();
include "db_conn.php"; 

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// 1. Kunin ang counts - Inupdate ang 'All' para kasama ang Ready for Dispatch (Tracker stage)[cite: 4]
$count_all = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status IN ('Pending', 'Preparing', 'Ready for Dispatch')"))['c'];
$count_incoming = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status = 'Pending'"))['c'];
$count_preparing = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status = 'Preparing'"))['c'];
$count_completed = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status IN ('On the Way', 'Completed')"))['c'];

// 2. I-set ang filter status - Idinagdag ang Ready for Dispatch sa default view[cite: 4]
$filter = $_GET['filter'] ?? 'all';
$query_status = "WHERE o.status IN ('Pending', 'Preparing', 'Ready for Dispatch')";
if ($filter == 'incoming') $query_status = "WHERE o.status = 'Pending'";
if ($filter == 'preparing') $query_status = "WHERE o.status = 'Preparing'";
if ($filter == 'completed') $query_status = "WHERE o.status IN ('On the Way', 'Completed')";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Dashboard - Ate Kabayan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Poppins:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        :root { 
            --primary-orange: #FFBD59; 
            --highlight-orange: #F4A42B; 
            --card-incoming: #3a86ff; 
            --card-preparing: #fb5607; 
            --card-completed: #38b000;
            --bg-cream: #FFF8E7;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Poppins', sans-serif; background: var(--bg-cream); color: #333; }

        header {
            background-color: var(--primary-orange);
            padding: 15px 5%;
            position: sticky; top: 0; z-index: 1000;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .logo { display: flex; align-items: center; gap: 12px; text-decoration: none; color: white; }
        .logo-circle { width: 45px; height: 45px; background: white; border-radius: 50%; overflow: hidden; border: 2px solid white; }
        .logo-circle img { width: 100%; height: 100%; object-fit: cover; }
        .logo h2 { font-family: 'Fredoka One', cursive; font-size: 1.1rem; text-transform: uppercase; }

        .dashboard-nav { display: flex; justify-content: center; gap: 15px; padding: 20px; background: white; }
        .tab { padding: 10px 25px; border-radius: 50px; font-weight: 800; font-size: 0.85rem; border: none; cursor: pointer; background: #eee; color: #666; text-decoration: none; }
        .tab.active { background: var(--highlight-orange); color: white; }

        .container { padding: 30px; }
        #kitchen-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; }

        .order-card { background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05); display: flex; flex-direction: column; border: 1px solid #ddd; }
        .card-header { padding: 12px 15px; color: white; font-weight: 900; font-size: 0.85rem; display: flex; justify-content: space-between; align-items: center; }
        .bg-incoming { background: var(--card-incoming); }
        .bg-preparing { background: var(--card-preparing); }
        .bg-ready { background: var(--card-completed); }

        .status-badge { background: rgba(255,255,255,0.9); color: #333; padding: 2px 10px; border-radius: 50px; font-size: 0.65rem; text-transform: uppercase; }

        .card-body { padding: 15px; flex-grow: 1; }
        .order-time { font-size: 0.7rem; font-weight: 800; color: #888; }
        .customer-info { font-size: 0.85rem; font-weight: 700; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 8px; color: #444; }

        .items-list { list-style: none; margin-bottom: 15px; }
        .items-list li { font-weight: 800; font-size: 0.95rem; margin-bottom: 5px; }
        
        .notes-box { font-size: 0.75rem; color: #999; font-style: italic; background: #fafafa; padding: 10px; border-radius: 8px; border-left: 3px solid var(--highlight-orange); }

        .prep-timer { text-align: center; margin: 15px 0; color: #e74c3c; font-weight: 900; font-size: 1.1rem; }

        .card-footer { padding: 15px; border-top: 1px solid #f5f5f5; display: flex; flex-direction: column; gap: 10px; }
        input { padding: 12px; border-radius: 10px; border: 2px solid #eee; font-weight: 600; outline: none; }
        .btn-action { padding: 12px; border-radius: 10px; border: none; font-weight: 800; font-size: 0.8rem; color: white; cursor: pointer; text-transform: uppercase; transition: 0.2s; }
        
        .btn-blue { background: var(--card-incoming); }
        .btn-orange { background: var(--card-preparing); }
        .btn-green { background: var(--card-completed); }
        .btn-row { display: flex; gap: 10px; }
        .btn-row .btn-action { flex: 1; }

        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); backdrop-filter: blur(5px);
            display: none; justify-content: center; align-items: center; z-index: 2000;
        }
        .modal-content {
            background: white; padding: 30px; border-radius: 25px; width: 90%; max-width: 400px;
            text-align: center; box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .modal-content h3 { font-family: 'Fredoka One', cursive; color: var(--highlight-orange); margin-bottom: 15px; }
        .modal-content input { width: 100%; margin: 15px 0; text-align: center; font-size: 1.2rem; }
        .modal-btns { display: flex; gap: 10px; }
        .modal-btns button { flex: 1; }
        .btn-cancel { background: #ccc; }
    </style>
</head>
<body>

    <header>
        <a href="homepage.php" class="logo">
            <div class="logo-circle"><img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772298452/logo_ate_kabayan_jtfqeg.jpg"></div>
            <h2>ATE KABAYAN</h2>
        </a>
        <div style="color:white; font-weight:800; font-size:0.9rem;">KITCHEN DASHBOARD</div>
    </header>

    <div class="dashboard-nav">
        <a href="?filter=all" class="tab <?php echo $filter == 'all' ? 'active' : ''; ?>">All (<?php echo $count_all; ?>)</a>
        <a href="?filter=incoming" class="tab <?php echo $filter == 'incoming' ? 'active' : ''; ?>">Incoming (<?php echo $count_incoming; ?>)</a>
        <a href="?filter=preparing" class="tab <?php echo $filter == 'preparing' ? 'active' : ''; ?>">Preparing (<?php echo $count_preparing; ?>)</a>
        <a href="?filter=completed" class="tab <?php echo $filter == 'completed' ? 'active' : ''; ?>">Completed (<?php echo $count_completed; ?>)</a>
    </div>

    <div class="container">
        <div id="kitchen-list">
            <?php
            $sql = "SELECT o.*, c.full_name 
                    FROM orders o 
                    JOIN create_acc c ON o.user_id = c.id 
                    $query_status 
                    ORDER BY o.created_at ASC";
            $res = mysqli_query($conn, $sql);

            while($row = mysqli_fetch_assoc($res)) {
                $status = $row['status'];
                // Color logic: Ang Ready for Dispatch ay magiging Green na[cite: 4]
                $bg_class = ($status == 'Pending') ? 'bg-incoming' : (($status == 'Preparing') ? 'bg-preparing' : 'bg-ready');
            ?>
                <div class="order-card">
                    <div class="card-header <?php echo $bg_class; ?>">
                        Order #<?php echo $row['daily_order_no']; ?>
                        <span class="status-badge"><?php echo $status; ?></span>
                    </div>

                    <div class="card-body">
                        <div class="order-time"><?php echo date('H:i', strtotime($row['created_at'])); ?></div>
                        <div class="customer-info">
                            <strong>Type:</strong> 
                            <span style="color: var(--card-preparing);">
                                <?php echo htmlspecialchars($row['order_type']); ?>
                            </span><br>
                            <strong>Customer:</strong> <?php echo htmlspecialchars($row['full_name']); ?><br>
                            <strong>Address:</strong> <?php echo htmlspecialchars($row['address'] ?? 'Parañaque City'); ?>
                        </div>

                        <ul class="items-list">
                            <?php
                            $order_id = $row['id'];
                            $item_sql = "SELECT * FROM order_items WHERE order_id = '$order_id'";
                            $item_res = mysqli_query($conn, $item_sql);
                            while($item = mysqli_fetch_assoc($item_res)) {
                                echo "<li><i class='fas fa-utensils'></i> " . $item['quantity'] . "x " . htmlspecialchars($item['food_name']) . "</li>";
                            }
                            ?>
                        </ul>

                        <?php if(!empty($row['delivery_note'])): ?>
                            <div class="notes-box">Notes: <?php echo htmlspecialchars($row['delivery_note']); ?></div>
                        <?php endif; ?>

                        <!-- TITIGIL ANG TIMER DITO: Dahil ang condition ay dapat 'Preparing' lang[cite: 4, 8] -->
                        <?php if($status == 'Preparing'): ?>
                            <div class="prep-timer">
                                PREPARATION TIME: <span class="timer-val" 
                                    data-start="<?php echo $row['prep_start_time']; ?>" 
                                    data-mins="<?php echo $row['prep_time']; ?>">00:00:00</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="card-footer">
                        <?php if($status == 'Pending'): ?>
                            <input type="number" id="time-<?php echo $row['id']; ?>" placeholder="Minutes to cook (e.g. 15)">
                            <button class="btn-action btn-blue" onclick="startPrep(<?php echo $row['id']; ?>)">Start Cooking</button>
                        <?php elseif($status == 'Preparing'): ?>
                            <div class="btn-row">
                                <button class="btn-action btn-orange" onclick="openExtendModal(<?php echo $row['id']; ?>)">Extend Time</button>
                                <button class="btn-action btn-orange" onclick="finishPrep(<?php echo $row['id']; ?>, '<?php echo $row['order_type']; ?>')">Finish Preparing</button>
                            </div>
                        <?php elseif($status == 'Ready for Dispatch'): ?>
                            <!-- ITO ANG TRACKER STAGE: Lilitaw ito agad pag-click ng Finish[cite: 4, 8] -->
                            <input type="text" id="link-<?php echo $row['id']; ?>" placeholder="Paste Tracking Link">
                            <button class="btn-action btn-green" onclick="dispatchOrder(<?php echo $row['id']; ?>)">Dispatch</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <div id="extendModal" class="modal-overlay">
        <div class="modal-content">
            <h3>EXTEND COOKING TIME</h3>
            <p>Ilang minuto ang idadagdag?</p>
            <input type="number" id="extraMinutesInput" placeholder="Minuto (e.g. 5)" min="1">
            <div class="modal-btns">
                <button class="btn-action btn-cancel" onclick="closeExtendModal()">Cancel</button>
                <button class="btn-action btn-orange" onclick="confirmExtend()">Extend Now</button>
            </div>
        </div>
    </div>

    <script>
        let currentOrderId = null;

        function updateTimers() {
            document.querySelectorAll('.timer-val').forEach(timer => {
                const start = timer.getAttribute('data-start');
                const mins = parseInt(timer.getAttribute('data-mins'));
                if(!start || !mins) return;

                const startTime = new Date(start.replace(/-/g, "/")).getTime();
                const endTime = startTime + (mins * 60 * 1000);
                const now = new Date().getTime();
                const distance = endTime - now;

                if (distance < 0) {
                    timer.innerText = "OVERDUE!";
                    timer.style.color = "red";
                } else {
                    const h = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const m = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const s = Math.floor((distance % (1000 * 60)) / 1000);
                    timer.innerText = `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
                    timer.style.color = "#e74c3c";
                }
            });
        }

        function startPrep(id) {
            const mins = document.getElementById('time-' + id).value;
            if(!mins) return alert("Paki-lagay ang oras.");
            sendAction(id, 'start_prep', { mins });
        }

        function openExtendModal(id) {
            currentOrderId = id;
            document.getElementById('extendModal').style.display = 'flex';
            document.getElementById('extraMinutesInput').value = '';
            document.getElementById('extraMinutesInput').focus();
        }

        function closeExtendModal() {
            document.getElementById('extendModal').style.display = 'none';
            currentOrderId = null;
        }

        function confirmExtend() {
            const extraMins = document.getElementById('extraMinutesInput').value;
            if (!extraMins || extraMins <= 0) return alert("Paki-lagay ng tamang minuto.");
            sendAction(currentOrderId, 'extend_time', { extra_mins: extraMins });
            closeExtendModal();
        }

        function finishPrep(id, type) {
            if(confirm("Luto na ba talaga ito, Kabayan?")) {
                const action = (type.trim().toLowerCase() === 'delivery') ? 'finish_prep' : 'complete_direct';
                sendAction(id, action);
            }
        }

        function dispatchOrder(id) {
            const link = document.getElementById('link-' + id).value;
            if(!link) return alert("Paki-paste ang tracking link.");
            sendAction(id, 'handover', { link });
        }

        function sendAction(id, action, extras = {}) {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('action', action);
            for (let key in extras) formData.append(key, extras[key]);

            fetch('update_kitchen_status.php', { method: 'POST', body: formData })
            .then(res => res.text())
            .then(data => {
                if(data.trim() === "success") {
                    location.reload();
                } else {
                    alert("Error: " + data);
                }
            })
            .catch(err => console.error(err));
        }

        setInterval(updateTimers, 1000);

        window.onclick = function(event) {
            if (event.target == document.getElementById('extendModal')) {
                closeExtendModal();
            }
        }
    </script>
</body>
</html>