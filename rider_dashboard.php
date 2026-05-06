<?php
session_start();
include "db_conn.php";
// Dito pwedeng i-check kung 'Rider' ang role ng naka-login
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rider Dashboard - Ate Kabayan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #F4A42B; --bg: #f8f9fa; }
        body { font-family: 'Poppins', sans-serif; background: var(--bg); margin: 0; padding: 20px; }
        h1 { font-weight: 800; color: #333; }
        .order-list { display: flex; flex-direction: column; gap: 15px; }
        .rider-card { background: white; border-radius: 20px; padding: 20px; border: 2px solid #000; box-shadow: 0 4px 0 #000; }
        .status-badge { background: var(--primary); color: white; padding: 5px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; }
        .customer-info h3 { margin: 10px 0 5px; font-size: 1.2rem; }
        .customer-info p { margin: 2px 0; color: #666; font-size: 0.9rem; }
        .actions { margin-top: 20px; display: flex; gap: 10px; }
        .btn { flex: 1; padding: 15px; border-radius: 12px; border: 2px solid #000; font-weight: 800; cursor: pointer; transition: 0.2s; text-align: center; text-decoration: none; }
        .btn-arrived { background: #3498db; color: white; }
        .btn-delivered { background: #27ae60; color: white; }
        .btn-call { background: white; color: #000; width: 60px; flex: none; }
    </style>
</head>
<body>

    <h1>🏍️ My Deliveries</h1>

    <div class="order-list">
        <?php
        // Kunin ang mga orders na 'On the way' o 'Arrived'
        $sql = "SELECT o.*, u.full_name, u.contact_number 
                FROM orders o 
                JOIN create_acc u ON o.user_id = u.id 
                WHERE o.status IN ('On the way', 'Arrived') 
                ORDER BY o.created_at DESC";
        $res = mysqli_query($conn, $sql);

        if (mysqli_num_rows($res) == 0) {
            echo "<p style='text-align:center; color:#888; margin-top:50px;'>No active deliveries. Chill muna!</p>";
        }

        while($row = mysqli_fetch_assoc($res)) {
            $status_label = ($row['status'] == 'On the way') ? 'Deliver Now' : 'At Location';
            echo "
            <div class='rider-card'>
                <span class='status-badge'>{$row['status']}</span>
                <div class='customer-info'>
                    <h3>Order #{$row['id']}</h3>
                    <p><i class='fa-solid fa-user'></i> <strong>{$row['full_name']}</strong></p>
                    <p><i class='fa-solid fa-location-dot'></i> {$row['address']}</p>
                    <p><i class='fa-solid fa-money-bill-wave'></i> PHP ".number_format($row['total_price'], 2)." ({$row['payment_method']})</p>
                </div>

                <div class='actions'>";
                
                if ($row['status'] == 'On the way') {
                    echo "<button class='btn btn-arrived' onclick='updateRiderStatus({$row['id']}, \"Arrived\")'>I have Arrived</button>";
                } else {
                    echo "<button class='btn btn-delivered' onclick='updateRiderStatus({$row['id']}, \"Delivered\")'>Mark as Delivered</button>";
                }

                echo "
                    <a href='tel:{$row['contact_number']}' class='btn btn-call'><i class='fa-solid fa-phone'></i></a>
                </div>
            </div>";
        }
        ?>
    </div>

    <script>
        function updateRiderStatus(orderId, newStatus) {
            const fd = new FormData();
            fd.append('id', orderId);
            fd.append('status', newStatus);

            fetch('update_rider_status.php', {
                method: 'POST',
                body: fd
            })
            .then(res => res.text())
            .then(data => {
                if(data === 'success') {
                    location.reload(); // Refresh para mag-update ang view
                }
            });
        }

        // Auto-refresh bawat 15 seconds para sa mga bagong handover galing kitchen
        setInterval(() => { location.reload(); }, 15000);
    </script>
</body>
</html>