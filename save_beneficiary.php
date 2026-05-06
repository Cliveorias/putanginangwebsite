<?php
session_start();
include "db_conn.php";

if (isset($_POST['b_name']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Gamit ang trim() para alisin ang spaces at empty() para icheck kung talagang may laman
    $b_name  = trim($_POST['b_name']);
    $b_type  = trim($_POST['b_type']);
    $b_id_no = trim($_POST['b_id_no']);

    // --- STRICK VALIDATION: Dito natin haharangin ---
    // Kung ang pangalan ay blangko, o ID number ay blangko, o walang in-upload na file
    if (empty($b_name) || empty($b_id_no) || empty($_FILES["b_id_pic"]["name"])) {
        header("Location: Profile.php?msg=IncompleteData");
        exit(); // STOP AGAD, hindi aabot sa INSERT query sa baba
    }

    $b_name  = mysqli_real_escape_string($conn, $b_name);
    $b_type  = mysqli_real_escape_string($conn, $b_type);
    $b_id_no = mysqli_real_escape_string($conn, $b_id_no);

    $target_dir = "uploads/ids/";
    if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }

    $file_name = time() . "_" . basename($_FILES["b_id_pic"]["name"]);
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["b_id_pic"]["tmp_name"], $target_file)) {
        // DITO LANG DAPAT MAG-INSERT PAG OKAY LAHAT
        $sql = "INSERT INTO user_discounts (user_id, beneficiary_name, discount_type, id_number, id_pic, status) 
                VALUES ('$user_id', '$b_name', '$b_type', '$b_id_no', '$target_file', 'Pending')";

        if (mysqli_query($conn, $sql)) {
            header("Location: Profile.php?msg=Success");
            exit();
        } else {
            die("Database Error: " . mysqli_error($conn));
        }
    } else {
        header("Location: Profile.php?msg=UploadError");
        exit();
    }
} else {
    header("Location: Profile.php?msg=SessionExpired");
    exit();
}
?>