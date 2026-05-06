<?php
session_start();
include "db_conn.php";

if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $user_id = $_SESSION['user_id'];

    // Siguraduhin na ang buburahin ay sa kanya ngang account
    $sql = "DELETE FROM user_discounts WHERE id = '$id' AND user_id = '$user_id'";
    if (mysqli_query($conn, $sql)) {
        header("Location: Profile.php?msg=Deleted");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>