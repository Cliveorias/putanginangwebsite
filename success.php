<?php
session_start();
include "db_conn.php";

// 1. Siguraduhin na naka-login ang user
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$new_order_id = 0;

/**
 * 2. DATABASE LOGIC: Pag-save ng Order
 * Nililinis ang cart pagkatapos ng matagumpay na bayad.
 */
if (isset($_SESSION['temp_order_details'])) {
    $o = $_SESSION['temp_order_details'];
    
    $amount = mysqli_real_escape_string($conn, $o['amount']);
    $addr   = mysqli_real_escape_string($conn, $o['address']);
    $meth   = mysqli_real_escape_string($conn, $o['method']);
    
    // INSERT sa orders table
    $sql = "INSERT INTO orders (user_id, total_price, payment_method, address, status, estimated_time) 
            VALUES ('$user_id', '$amount', '$meth', '$addr', 'Pending', 'Calculating...')";
    
    if (mysqli_query($conn, $sql)) {
        $new_order_id = mysqli_insert_id($conn); // Kinukuha ang ID para sa tracking page

        // BURAHIN ANG CART SA DATABASE (user_cart) para malinis na ang account
        mysqli_query($conn, "DELETE FROM user_cart WHERE user_id = '$user_id'");
        
        // LINISIN ANG SESSION DATA ng payment
        unset($_SESSION['temp_order_details']);
    }
} else {
    // Fallback: Kunin ang pinaka-latest order kung nag-refresh ang page
    $check_latest = mysqli_query($conn, "SELECT id FROM orders WHERE user_id = '$user_id' ORDER BY created_at DESC LIMIT 1");
    $latest = mysqli_fetch_assoc($check_latest);
    $new_order_id = $latest['id'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salamat Kabayan! - Success</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700;900&display=swap" rel="stylesheet">
    
    <style>
        /* --- CSS STYLES --- */
        :root {
            --primary: #F4A42B;
            --bg: #FFF8E7;
            --success: #2ecc71;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { 
            background: var(--bg); 
            font-family: 'Poppins', sans-serif;
            color: #333;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
        }

        .card { 
            background: white; 
            padding: 50px 30px; 
            border-radius: 30px; 
            width: 100%;
            max-width: 450px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            border: 2px solid #000;
            position: relative;
        }

        .icon-success { 
            color: var(--success); 
            font-size: 90px; 
            margin-bottom: 20px;
            animation: bounceIn 0.8s ease;
        }

        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.05); opacity: 1; }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); }
        }

        h1 { 
            font-weight: 900; 
            font-size: 2rem;
            margin-bottom: 10px; 
            color: #000; 
        }

        p { 
            margin-bottom: 30px; 
            color: #666;
            line-height: 1.5;
        }

        .btn-track { 
            display: inline-block;
            background: var(--primary); 
            color: #fff; 
            padding: 18px 40px; 
            border-radius: 18px; 
            font-weight: 800; 
            text-decoration: none;
            transition: 0.3s;
            border: 2px solid #000;
            box-shadow: 0 5px 0 #d35400;
            font-size: 1.1rem;
        }

        .btn-track:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 8px 0 #d35400;
        }

        .btn-track:active {
            transform: translateY(2px);
            box-shadow: none;
        }

        .redirect-msg { 
            margin-top: 25px; 
            font-size: 0.85rem; 
            color: #888;
            font-style: italic;
        }

        /* FoodPanda-like loader bar */
        .progress-bar {
            width: 100%;
            height: 6px;
            background: #eee;
            margin-top: 20px;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: var(--primary);
            width: 0%;
            animation: fillProgress 5s linear forwards;
        }

        @keyframes fillProgress {
            to { width: 100%; }
        }
    </style>
</head>
<body>

    <div class="card">
        <div class="icon-success">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        
        <h1>Salamat Kabayan!</h1>
        <p>Natanggap na namin ang iyong bayad. Wait lang sa luto, ihahanda na namin ang iyong ulam!</p>
        
        <a href="preparing.php?id=<?php echo $new_order_id; ?>" class="btn-track">Track My Order</a>
        
        <div class="progress-bar">
            <div class="progress-fill"></div>
        </div>
        
        <p class="redirect-msg">Kabayan, dadalhin ka namin sa kusina status sa loob ng 5 segundo...</p>
    </div>

    <script>
        // --- JAVASCRIPT LOGIC ---

        // 1. Linisin ang LocalStorage Carts
        const currentUserId = "<?php echo $user_id; ?>";
        const orderId = "<?php echo $new_order_id; ?>";
        
        localStorage.removeItem('myCart');               // Universal
        localStorage.removeItem('cart_guest');           // Guest
        localStorage.removeItem('cart_' + currentUserId); // User Specific
        
        console.log("LocalStorage cleared. Order ID: " + orderId);

        // 2. Automatic Redirect to preparing.php
        setTimeout(() => {
            if (orderId != "0") {
                window.location.href = 'preparing.php?id=' + orderId;
            } else {
                window.location.href = 'homepage.php';
            }
        }, 5000); // 5 seconds wait
    </script>
</body>
</html>