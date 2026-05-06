<?php
include "db_conn.php";

$action = $_POST['action'] ?? '';

if ($action == 'register_staff') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $password = $_POST['password']; 

    // 1. I-save sa create_acc para makapag-log in sila
    // Siguraduhin na ang table mo ay may 'role' column (VARCHAR)
    $sql_acc = "INSERT INTO create_acc (full_name, email, contact_no, password, role) 
                VALUES ('$name', '$email', '$contact', '$password', '$role')";
    
    if (mysqli_query($conn, $sql_acc)) {
        $user_id = mysqli_insert_id($conn); // Kunin ang ID na ginawa para sa link

        // 2. I-save sa staff table para sa Directory display
        $sql_staff = "INSERT INTO staff (name, role, contact, email, status) 
                      VALUES ('$name', '$role', '$contact', '$email', 'Off Duty')";
        
        if (mysqli_query($conn, $sql_staff)) {
            echo "success";
        } else {
            echo "Error sa staff table: " . mysqli_error($conn);
        }
    } else {
        echo "Error sa account table: " . mysqli_error($conn);
    }
}

if ($action == 'delete_staff') {
    $id = $_POST['id'];
    
    // Kunin muna ang email para mabura din sa create_acc
    $res = mysqli_query($conn, "SELECT email FROM staff WHERE id = '$id'");
    $row = mysqli_fetch_assoc($res);
    $email = $row['email'];

    // Burahin sa parehong table
    mysqli_query($conn, "DELETE FROM staff WHERE id = '$id'");
    mysqli_query($conn, "DELETE FROM create_acc WHERE email = '$email'");
    
    echo "success";
}
?>