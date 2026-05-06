<?php
session_start();
include "db_conn.php";

if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $status = mysqli_real_escape_string($conn, $_GET['status']);

    $sql = "UPDATE user_discounts SET status = '$status' WHERE id = '$id'";

    if (mysqli_query($conn, $sql)) {
        header("Location: admin_dashboard.php?msg=Updated");
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
}
?>