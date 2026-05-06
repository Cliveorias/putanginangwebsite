<?php
session_start();
include "db_conn.php"; //

if (isset($_POST['rating'])) {
    $user_id = $_SESSION['user_id'];
    $order_id = $_POST['order_id'];
    $rating = $_POST['rating'];
    $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);
    // Handle Image Upload
    $image_path = "";
    if (isset($_FILES['feedback_image']) && $_FILES['feedback_image']['error'] == 0) {
        $target_dir = "uploads/ratings/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); } // Gawa ng folder kung wala pa
        
        $file_name = time() . "_" . $_FILES['feedback_image']['name'];
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES['feedback_image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        }
    }

    // Insert sa Database
    $sql = "INSERT INTO ratings (user_id, order_id, rating, feedback, feedback_image) 
            VALUES ('$user_id', '$order_id', '$rating', '$feedback', '$image_path')";

    if (mysqli_query($conn, $sql)) {
        header("Location: ratings.php?success=1"); // Balik sa ratings page
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>