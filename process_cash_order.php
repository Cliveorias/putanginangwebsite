<?php
session_start();
include "db_conn.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    
    // Kunin ang mga detalye mula sa POST
    $total_price = mysqli_real_escape_string($conn, $_POST['total_price']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    
    // FIX: 'notes' sa JS, pero 'delivery_note' sa database mo
    $delivery_note = mysqli_real_escape_string($conn, $_POST['notes']);
    
    $order_type = mysqli_real_escape_string($conn, $_POST['order_type']); // "Delivery" o "Pick Up"

    // --- LOGIC PARA SA DAILY RESET NG ORDER ID ---
    $today = date('Y-m-d');
    $count_sql = "SELECT COUNT(*) as total FROM orders WHERE DATE(created_at) = '$today'";
    $count_res = mysqli_query($conn, $count_sql);
    $count_row = mysqli_fetch_assoc($count_res);
    $daily_order_no = $count_row['total'] + 1;

    // 1. I-save ang Main Order sa 'orders' table (TINANGGAL ANG pickup_sub_type DITO)[cite: 13]
    $sql = "INSERT INTO orders (user_id, order_type, address, delivery_note, total_price, payment_method, daily_order_no, status, created_at) 
            VALUES ('$user_id', '$order_type', '$address', '$delivery_note', '$total_price', '$payment_method', '$daily_order_no', 'Pending', NOW())";

    if (mysqli_query($conn, $sql)) {
        // 2. Kunin ang ID ng order para sa order_items
        $order_id = mysqli_insert_id($conn);

        // 3. Decode ang cart_data
        $cart = json_decode($_POST['cart_data'], true);

        if (!empty($cart)) {
            foreach ($cart as $item) {
                $food_name = mysqli_real_escape_string($conn, $item['name']);
                $quantity = (int)$item['qty'];
                $price = (float)$item['price'];

                // 4. I-insert sa separate 'order_items' table para makita sa Dashboard
                $item_sql = "INSERT INTO order_items (order_id, food_name, quantity, price) 
                             VALUES ('$order_id', '$food_name', '$quantity', '$price')";
                mysqli_query($conn, $item_sql);
            }
        }

        // 5. Success redirect
        mysqli_query($conn, "DELETE FROM user_cart WHERE user_id = '$user_id'");
        header("Location: success.php?method=cash&order_id=" . $order_id);
        exit();
    } else {
        die("Database Error: " . mysqli_error($conn));
    }
}
?>