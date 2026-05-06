<?php
include "db_conn.php";

if (isset($_POST['order_id']) && isset($_POST['lat']) && isset($_POST['lng'])) {
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $lat = mysqli_real_escape_string($conn, $_POST['lat']);
    $lng = mysqli_real_escape_string($conn, $_POST['lng']);

    $sql = "UPDATE orders SET rider_lat = '$lat', rider_lng = '$lng' WHERE id = '$order_id'";
    
    if (mysqli_query($conn, $sql)) {
        echo "Location Updated";
    }
}
?>