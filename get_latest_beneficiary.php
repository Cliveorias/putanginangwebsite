<?php
session_start();
include "db_conn.php";
$user_id = $_SESSION['user_id'];

// Kunin ang pinakabagong record na wala pang Face ID
$sql = "SELECT id, id_pic FROM user_discounts 
        WHERE user_id = '$user_id' AND face_descriptor IS NULL 
        ORDER BY id DESC LIMIT 1";

$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);

header('Content-Type: application/json');
echo json_encode($data ? $data : ['id' => null]);
?>