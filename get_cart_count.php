<?php
session_start();
include "db_conn.php";

$total = 0;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    // FIXED: user_cart table
    $res = mysqli_query($conn, "SELECT SUM(qty) as total FROM user_cart WHERE user_id = '$user_id'");
    $row = mysqli_fetch_assoc($res);
    $total = $row['total'] ?? 0;
}
echo json_encode(['total_qty' => (int)$total]);
?>