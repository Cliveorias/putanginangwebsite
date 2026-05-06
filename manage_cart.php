<?php
session_start();
include "db_conn.php";

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo "not_logged_in";
    exit();
}

$action = $_POST['action'] ?? '';

if ($action == 'add') {
    $name  = mysqli_real_escape_string($conn, $_POST['name']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $img   = mysqli_real_escape_string($conn, $_POST['img']);

    // Check kung nandoon na ang item sa cart ng user
    $check = mysqli_query($conn, "SELECT id FROM user_cart WHERE user_id='$user_id' AND item_name='$name'");
    
    if (mysqli_num_rows($check) > 0) {
        // Kung nandoon na, dagdagan ang qty
        mysqli_query($conn, "UPDATE user_cart SET qty = qty + 1 WHERE user_id='$user_id' AND item_name='$name'");
    } else {
        // Kung wala pa, i-insert bilang bagong row
        mysqli_query($conn, "INSERT INTO user_cart (user_id, item_name, price, qty, image_url) VALUES ('$user_id', '$name', '$price', 1, '$img')");
    }
    echo "success";
}

if ($action == 'update_qty') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $amount = (int)$_POST['amount'];
    
    mysqli_query($conn, "UPDATE user_cart SET qty = qty + ($amount) WHERE user_id='$user_id' AND item_name='$name'");
    
    // Burahin ang item kung 0 na ang quantity
    mysqli_query($conn, "DELETE FROM user_cart WHERE qty <= 0");
    echo "success";
}

if ($action == 'get_count') {
    $res = mysqli_query($conn, "SELECT SUM(qty) as total FROM user_cart WHERE user_id='$user_id'");
    $row = mysqli_fetch_assoc($res);
    echo $row['total'] ?? 0;
}
?>