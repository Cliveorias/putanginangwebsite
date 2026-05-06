<?php
session_start();
include "db_conn.php"; 

// Kunin ang counts para sa tabs
$count_all = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status != 'Cancelled'"))['c'];
$count_incoming = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status = 'Pending'"))['c'];
$count_preparing = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status = 'Preparing'"))['c'];
$count_completed = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status = 'Completed'"))['c'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Dashboard - Kainan ni Ate Kabayan</title>
    <link rel="stylesheet" href="kitchen.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<header class="kitchen-header">
    <div class="header-left">
        <div class="logo-circle">
            <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772298452/logo_ate_kabayan_jtfqeg.jpg" alt="Logo">
        </div>
        <div class="title-group">
            <h1>KITCHEN DASHBOARD</h1>
            <p>Ate Kabayan Management System</p>
        </div>
    </div>
    
    <div class="filter-tabs">
        <button class="tab active" onclick="filterStatus('all')">All <span class="count" id="count-all"><?php echo $count_all; ?></span></button>
        <button class="tab" onclick="filterStatus('Pending')">Incoming <span class="count" id="count-incoming"><?php echo $count_incoming; ?></span></button>
        <button class="tab" onclick="filterStatus('Preparing')">Preparing <span class="count" id="count-preparing"><?php echo $count_preparing; ?></span></button>
        <button class="tab" onclick="filterStatus('Completed')">Completed <span class="count" id="count-completed"><?php echo $count_completed; ?></span></button>
    </div>
</header>

<main class="kitchen-grid" id="order-grid">
    </main>

<audio id="order-sound" src="notification.mp3" preload="auto"></audio>

<script>
    // Ipasa ang initial status filter
    let currentFilter = 'all';

    function filterStatus(status) {
        currentFilter = status;
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        event.currentTarget.classList.add('active');
        fetchOrders();
    }

    // Function para i-update ang countdown timers sa UI
    function updateTimers() {
        document.querySelectorAll('.timer-display').forEach(display => {
            const startTime = parseInt(display.dataset.start) * 1000; // to ms
            const duration = parseInt(display.dataset.duration) * 60 * 1000; // to ms
            const now = new Date().getTime();
            
            const distance = (startTime + duration) - now;

            if (distance < 0) {
                display.innerHTML = "READY FOR PICKUP";
                display.style.color = "#C92C1C";
            } else {
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                display.innerHTML = minutes + "m " + seconds + "s remaining";
            }
        });
    }

    setInterval(updateTimers, 1000);
</script>

<script src="kitchen.js"></script>
</body>
</html>