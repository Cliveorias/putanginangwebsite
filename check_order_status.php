<?php
include "db_conn.php";

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Idinagdag ang prep_time at prep_start_time para mabasa ng countdown script
    $sql = "SELECT status, estimated_time, prep_time, prep_start_time, rider_lat, rider_lng 
            FROM orders 
            WHERE id = '$id'";
            
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    header('Content-Type: application/json');
    echo json_encode($row);
}
?>