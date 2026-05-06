<?php
// process_kitchen.php
include "db_conn.php";

$action = $_POST['action'] ?? '';
$id = (int)($_POST['id'] ?? 0);

if ($action == 'start_prep' && $id > 0) {
    $mins = (int)$_POST['mins'];
    $sql = "UPDATE orders SET status='Preparing', prep_time=$mins, prep_start_time=NOW(), estimated_time='$mins mins' WHERE id=$id";
    mysqli_query($conn, $sql);
}

// BAGONG ACTION: Para sabay na ang Link at Status
if ($action == 'handover' && $id > 0) {
    $link = mysqli_real_escape_string($conn, $_POST['link']);
    // Dito natin sisiguraduhin na 'On the Way' ang malalagay sa status
    $sql = "UPDATE orders SET tracking_link='$link', status='On the Way' WHERE id=$id";
    
    if(mysqli_query($conn, $sql)) {
        echo "Success";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>