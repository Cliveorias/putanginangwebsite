<?php
include "db_conn.php";

$filter = $_GET['filter'] ?? 'all';
$sql = "SELECT o.*, u.full_name FROM orders o JOIN create_acc u ON o.user_id = u.id";

if ($filter !== 'all') {
    $sql .= " WHERE o.status = '$filter'";
}
$sql .= " ORDER BY o.created_at DESC";

$result = mysqli_query($conn, $sql);
$orders = [];

while($row = mysqli_fetch_assoc($result)) {
    // I-format ang timestamp para sa JavaScript countdown
    $row['prep_start_unix'] = $row['prep_start_time'] ? strtotime($row['prep_start_time']) : null;
    $orders[] = $row;
}

echo json_encode($orders);