<?php
session_start();
include "db_conn.php";

if (isset($_POST['face_data']) && isset($_POST['b_id'])) {
    $b_id = mysqli_real_escape_string($conn, $_POST['b_id']);
    $face_data = mysqli_real_escape_string($conn, $_POST['face_data']);

    // I-update ang specific beneficiary record gamit ang Face Descriptor
    $sql = "UPDATE user_discounts SET face_descriptor = '$face_data' WHERE id = '$b_id'";

    if (mysqli_query($conn, $sql)) {
        echo "Success";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    echo "Error: Missing Data.";
}
?>