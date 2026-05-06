<?php
session_start();
include "db_conn.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $sender_type = mysqli_real_escape_string($conn, $_POST['sender_type']); // 'Customer' o 'Admin'
    
    // Kung customer ang nag-send, kunin ang sarili niyang user_id
    if ($sender_type === 'Customer' && isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    } 
    // Kung admin ang nag-send, kailangan ang ID ng customer na nirereplyan
    elseif ($sender_type === 'Admin' && isset($_POST['customer_id'])) {
        $user_id = mysqli_real_escape_string($conn, $_POST['customer_id']);
    } else {
        echo "error: unauthorized";
        exit();
    }

    if (!empty($message)) {
        $sql = "INSERT INTO chat_messages (user_id, sender_type, message) VALUES ('$user_id', '$sender_type', '$message')";
        if(mysqli_query($conn, $sql)) {
            echo "success";
        } else {
            echo "error";
        }
    }
}
?>