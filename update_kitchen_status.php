<?php
session_start(); // Idinagdag para makuha ang pangalan ng naka-login na staff
include "db_conn.php"; // Siguraduhing tama ang path

if (isset($_POST['id']) && isset($_POST['action'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $action = $_POST['action'];
    $sql = "";
    
    // Idinagdag: Variable para sa ilalagay sa Activity Log
    $log_action = ""; 

    switch ($action) {
        case 'start_prep':
            // STEP 1: Mula Pending tungong Preparing
            $mins = mysqli_real_escape_string($conn, $_POST['mins']);
            $sql = "UPDATE orders SET 
                    status = 'Preparing', 
                    prep_time = '$mins', 
                    prep_start_time = NOW() 
                    WHERE id = '$id'";
            $log_action = "Order Accepted / Started Cooking";
            break;

        case 'extend_time':
            // Idadagdag ang extra minutes sa kasalukuyang prep_time
            $extra_mins = mysqli_real_escape_string($conn, $_POST['extra_mins']);
            $sql = "UPDATE orders SET 
                    prep_time = prep_time + '$extra_mins' 
                    WHERE id = '$id'";
            $log_action = "Extended Cooking Time";
            break;

        case 'finish_prep':
            // STEP 2: Mula Preparing tungong Ready for Dispatch (Delivery)
            $sql = "UPDATE orders SET status = 'Ready for Dispatch' WHERE id = '$id'";
            $log_action = "Finished Preparing";
            break;

        case 'complete_direct':
            // Mula Preparing derechong Completed (Pick-up/Dine-in)
            $sql = "UPDATE orders SET status = 'Completed' WHERE id = '$id'";
            $log_action = "Completed Order";
            break;

        case 'handover':
            // STEP 3: Mula Ready for Dispatch tungong On the Way
            $link = mysqli_real_escape_string($conn, $_POST['link']);
            $sql = "UPDATE orders SET 
                    status = 'On the Way', 
                    tracking_link = '$link' 
                    WHERE id = '$id'";
            $log_action = "Order Dispatched";
            break;

        default:
            echo "error: invalid_action";
            exit();
    }

    if (!empty($sql) && mysqli_query($conn, $sql)) {
        
        // --- SIMULA NG ACTIVITY LOG LOGIC ---
        // Kunin ang daily_order_no para idisplay sa log
        $order_query = mysqli_query($conn, "SELECT daily_order_no FROM orders WHERE id = '$id'");
        if ($order_query && mysqli_num_rows($order_query) > 0) {
            $order_data = mysqli_fetch_assoc($order_query);
            $display_id = "#ORD-" . $order_data['daily_order_no'];
        } else {
            $display_id = "#ORD-" . $id;
        }

        // Kunin ang staff name sa session. Kung hindi pa naka-set, default sa 'Kitchen Staff'
        $staff_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : "Kitchen Staff";

        // I-save sa database
        $insert_log = "INSERT INTO staff_activity_log (staff_name, action, order_id) 
                       VALUES ('$staff_name', '$log_action', '$display_id')";
        mysqli_query($conn, $insert_log);
        // --- WAKAS NG ACTIVITY LOG LOGIC ---

        echo "success";
    } else {
        echo "error: " . mysqli_error($conn);
    }
}
?>