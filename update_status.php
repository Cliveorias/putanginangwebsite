<?php
include "db_conn.php"; // Koneksyon sa database[cite: 1, 8]

if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $status = mysqli_real_escape_string($conn, $_GET['status']);
    
    // I-u-update ang database[cite: 8, 9]
    $query = "UPDATE menu_items SET availability = '$status' WHERE id = '$id'";
    
    if(mysqli_query($conn, $query)) {
        echo "Success"; // Ito ang matatanggap ng Javascript
    } else {
        echo "Error";
    }
}
?>