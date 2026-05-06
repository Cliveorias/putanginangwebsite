<?php
include "db_conn.php";

// Siniguro nating integer ang kukunin para iwas-error sa query
$order_id = (int)($_GET['order_id'] ?? 0);

// Kukunin ang mga items base sa Order ID - Hindi apektado ng pagbura sa pickup_sub_type
$sql = "SELECT oi.food_name, oi.quantity, oi.price, m.image_url 
        FROM order_items oi 
        JOIN menu_items m ON oi.food_name = m.name 
        WHERE oi.order_id = '$order_id'";

$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    while ($item = mysqli_fetch_assoc($result)) {
        echo "<div style='display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; padding:10px; background:#fffbf0; border-radius:10px; border:1.5px solid #ffbd59;'>";
        echo "<div style='display:flex; align-items:center; gap:10px;'>";
        echo "<img src='{$item['image_url']}' style='width:40px; height:40px; border-radius:5px; object-fit:cover;'>";
        echo "<span><strong>{$item['quantity']}x</strong> {$item['food_name']}</span>";
        echo "</div>";
        echo "<strong>₱" . number_format($item['price'] * $item['quantity'], 2) . "</strong>";
        echo "</div>";
    }
} else {
    echo "<p style='color:#888; text-align:center;'>No items found for this order.</p>";
}
?>