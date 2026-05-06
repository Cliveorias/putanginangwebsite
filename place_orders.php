<?php
session_start();
include "db_conn.php";

// Kunin ang data mula sa AJAX (Checkout.js)
$user_id = $_SESSION['user_id'];
$order_type = $_POST['order_type']; // Dapat "Delivery" o "Pick Up"
$pickup_sub_type = $_POST['pickup_sub_type'] ?? ""; // Dine In o Take Out
$address = $_POST['address'];
$total_price = $_POST['total_price'];
$payment_method = $_POST['payment_method'];

// 1. I-save muna ang main order sa 'orders' table
$sql = "INSERT INTO orders (user_id, order_type, pickup_sub_type, address, total_price, payment_method, status, created_at) 
        VALUES ('$user_id', '$order_type', '$pickup_sub_type', '$address', '$total_price', '$payment_method', 'Pending', NOW())";

if (mysqli_query($conn, $sql)) {
    $order_id = mysqli_insert_id($conn); // Kunin ang ID ng bagong order

    // 2. IMPORTANT: I-save ang mga items mula sa cart
    // Ang 'cart_data' ay dapat pinasa galing sa localStorage ng Checkout.js
    $cart_items = json_decode($_POST['cart_data'], true);

    foreach ($cart_items as $item) {
        $food_name = mysqli_real_escape_string($conn, $item['name']);
        $quantity = $item['quantity'];
        $price = $item['price'];

        $item_sql = "INSERT INTO order_items (order_id, food_name, quantity, price) 
                     VALUES ('$order_id', '$food_name', '$quantity', '$price')";
        mysqli_query($conn, $item_sql);
    }
    echo "success";
} else {
    echo "error";
}
?>