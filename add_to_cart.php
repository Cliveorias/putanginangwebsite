<?php
session_start();
include "db_conn.php";

if (isset($_SESSION['user_id']) && isset($_POST['name'])) {
    $user_id = $_SESSION['user_id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = $_POST['price'];
    $img = mysqli_real_escape_string($conn, $_POST['img']);

    // Check kung nandoon na sa user_cart
    $check = mysqli_query($conn, "SELECT id FROM user_cart WHERE user_id = '$user_id' AND item_name = '$name'");
    
    if (mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "UPDATE user_cart SET qty = qty + 1 WHERE user_id = '$user_id' AND item_name = '$name'");
    } else {
        // FIXED: Ginamit ang 'image_url' base sa table mo
        mysqli_query($conn, "INSERT INTO user_cart (user_id, item_name, price, image_url, qty) VALUES ('$user_id', '$name', '$price', '$img', 1)");
    }
    echo json_encode(['status' => 'success']);
}
?>