<?php
session_start();
include "db_conn.php";

// 1. Siguraduhin na Rider lang ang makakapasok
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'rider') {
    header("Location: login.html");
    exit();
}

$rider_name = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rider Dashboard - Ate Kabayan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800;900&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #F4A42B;
            --secondary: #27ae60;
            --nav-blue: #4285F4; /* Google Maps Blue */
            --bg: #FFF8E7;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: var(--bg); padding-bottom: 50px; }

        header { 
            background: var(--primary); 
            padding: 20px; 
            color: white; 
            border-bottom: 3px solid #000;
            position: sticky; top: 0; z-index: 100;
            display: flex; justify-content: space-between; align-items: center;
        }

        .container { padding: 15px; }
        .welcome { margin-bottom: 20px; }
        .welcome h1 { font-size: 1.5rem; font-weight: 800; }

        .delivery-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            border: 2px solid #000;
            margin-bottom: 20px;
            box-shadow: 0 8px 0 rgba(0,0,0,0.05);
        }

        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .status-tag { 
            background: var(--secondary); 
            color: white; 
            padding: 5px 12px; 
            border-radius: 50px; 
            font-size: 0.7rem; 
            font-weight: 900; 
            text-transform: uppercase; 
        }

        .customer-details h3 { font-size: 1.1rem; font-weight: 800; margin-bottom: 5px; }
        .customer-details p { font-size: 0.85rem; color: #555; margin-bottom: 5px; display: flex; align-items: center; gap: 8px; }

        .actions { display: flex; flex-direction: column; gap: 10px; margin-top: 20px; }
        .btn { 
            padding: 15px; 
            border-radius: 12px; 
            border: 2px solid #000; 
            font-weight: 800; 
            cursor: pointer; 
            text-align: center;
            text-decoration: none;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        /* MGA KULAY NG BUTTONS */
        .btn-nav { background: var(--nav-blue); color: white; } /* NAVIGATE */
        .btn-gps { background: #3498db; color: white; } /* BROADCAST GPS */
        .btn-done { background: var(--secondary); color: white; } /* DELIVERED */
        .btn-call { background: white; color: #000; }

        .tracking-active {
            background: #e74c3c !important;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body>

<header>
    <h2>🏍️ RIDER TRACKER</h2>
    <a href="logout.php" style="color:white; font-size:0.8rem; font-weight:700;">Log Out</a>
</header>

<div class="container">
    <div class="welcome">
        <p>Mabuhay, <strong><?php echo htmlspecialchars($rider_name); ?></strong>!</p>
        <h1>Active Orders</h1>
    </div>

    <div id="delivery-list">
        <?php
        $sql = "SELECT o.*, u.full_name as customer_name, u.contact_number 
                FROM orders o 
                JOIN create_acc u ON o.user_id = u.id 
                WHERE o.status IN ('On the way', 'Arrived', 'Preparing')";
        $res = mysqli_query($conn, $sql);

        while($row = mysqli_fetch_assoc($res)): 
            // LOGIC: Kung may pinned location (cust_lat/lng), gamitin yun. Kung wala, gamitin ang address string.
            $destination = (!empty($row['cust_lat']) && !empty($row['cust_lng'])) 
                ? $row['cust_lat'] . "," . $row['cust_lng'] 
                : urlencode($row['address']);
        ?>
            <div class="delivery-card" id="order-<?php echo $row['id']; ?>">
                <div class="card-header">
                    <span class="status-tag"><?php echo $row['status']; ?></span>
                    <small>Order #<?php echo $row['id']; ?></small>
                </div>

                <div class="customer-details">
                    <h3><?php echo htmlspecialchars($row['customer_name']); ?></h3>
                    <p><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($row['address']); ?></p>
                    <p><i class="fa-solid fa-phone"></i> <?php echo $row['contact_number']; ?></p>
                </div>

                <div class="actions">
                    <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo $destination; ?>" 
                       target="_blank" class="btn btn-nav">
                        <i class="fa-solid fa-map-location-dot"></i> NAVIGATE TO CUSTOMER
                    </a>

                    <a href="tel:<?php echo $row['contact_number']; ?>" class="btn btn-call">
                        <i class="fa-solid fa-phone"></i> CALL CUSTOMER
                    </a>

                    <button class="btn btn-gps" id="gps-btn-<?php echo $row['id']; ?>" onclick="toggleTracking(<?php echo $row['id']; ?>)">
                        <i class="fa-solid fa-location-crosshairs"></i> BROADCAST MY GPS
                    </button>

                    <button class="btn btn-done" onclick="completeOrder(<?php echo $row['id']; ?>)">
                        <i class="fa-solid fa-check-circle"></i> MARK AS DELIVERED
                    </button>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
    let watchID = null;

    function toggleTracking(orderId) {
        const btn = document.getElementById('gps-btn-' + orderId);

        if (watchID === null) {
            if ("geolocation" in navigator) {
                btn.innerHTML = '<i class="fa-solid fa-stop"></i> STOP BROADCASTING';
                btn.classList.add('tracking-active');

                updateOrderStatus(orderId, 'On the way');

                watchID = navigator.geolocation.watchPosition((position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    const fd = new FormData();
                    fd.append('order_id', orderId);
                    fd.append('lat', lat);
                    fd.append('lng', lng);

                    fetch('update_location.php', { method: 'POST', body: fd })
                    .then(res => console.log("Broadcasting location to Customer..."));

                }, (err) => {
                    alert("GPS Error: Pakicheck ang location settings mo.");
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000
                });
            }
        } else {
            navigator.geolocation.clearWatch(watchID);
            watchID = null;
            btn.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i> BROADCAST MY GPS';
            btn.classList.remove('tracking-active');
        }
    }

    function updateOrderStatus(id, status) {
        const fd = new FormData();
        fd.append('id', id);
        fd.append('status', status);
        fetch('update_rider_status.php', { method: 'POST', body: fd });
    }

    function completeOrder(id) {
        if(confirm("Nadeliver na ba talaga?")) {
            if (watchID !== null) navigator.geolocation.clearWatch(watchID);
            updateOrderStatus(id, 'Delivered');
            location.reload();
        }
    }
</script>

</body>
</html>