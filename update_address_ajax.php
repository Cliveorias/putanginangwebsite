<?php
session_start();
include "db_conn.php";

if (isset($_POST['type']) && isset($_POST['address']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $column = $_POST['type']; // 'address_home' o 'address_work'
    $new_addr = mysqli_real_escape_string($conn, $_POST['address']);
    
    // Kunin ang lat at lng mula sa AJAX request
    $lat = isset($_POST['lat']) ? mysqli_real_escape_string($conn, $_POST['lat']) : "";
    $lng = isset($_POST['lng']) ? mysqli_real_escape_string($conn, $_POST['lng']) : "";

    // Listahan ng mga valid columns para sa security
    $allowed_columns = ['address_home', 'address_work'];

    if (in_array($column, $allowed_columns)) {
        // Tukuyin kung anong coordinate columns ang ia-update base sa type
        $lat_col = ($column === 'address_home') ? 'lat_home' : 'lat_work';
        $lng_col = ($column === 'address_home') ? 'lng_home' : 'lng_work';

        // I-update ang address kasama ang coordinates para hindi mawala ang pin location
        $sql = "UPDATE create_acc SET 
                $column = '$new_addr', 
                $lat_col = '$lat', 
                $lng_col = '$lng' 
                WHERE id = '$user_id'";
        
        if (mysqli_query($conn, $sql)) {
            echo "success";
        } else {
            echo "error: " . mysqli_error($conn);
        }
    } else {
        echo "invalid_column";
    }
} else {
    echo "missing_data";
}
?>