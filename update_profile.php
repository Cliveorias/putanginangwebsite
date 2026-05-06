<?php
session_start();
include "db_conn.php";

// 1. Check kung may active session
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- 1. FB SYNC HANDLER ---
if (isset($_POST['fb_image_url'])) {
    $fb_url = mysqli_real_escape_string($conn, $_POST['fb_image_url']);
    mysqli_query($conn, "UPDATE create_acc SET profile_pic = '$fb_url' WHERE id = '$user_id'");
    echo "success";
    exit();
}

// --- 2. REMOVE PHOTO ---
if (isset($_GET['remove_photo'])) {
    mysqli_query($conn, "UPDATE create_acc SET profile_pic = '' WHERE id = '$user_id'");
    header("Location: Profile.php");
    exit();
}

// --- NEW: CHANGE PASSWORD HANDLER ---
if (isset($_POST['btn_change_password'])) {
    $current_pass = mysqli_real_escape_string($conn, $_POST['current_password']);
    $new_pass     = mysqli_real_escape_string($conn, $_POST['new_password']);
    $confirm_pass = mysqli_real_escape_string($conn, $_POST['confirm_new_password']);

    // Kunin ang current password hash at social login info
    $sql = "SELECT password, google_id, facebook_id FROM create_acc WHERE id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    // a. Block kung Social User (Google/FB)
    if (!empty($row['google_id']) || !empty($row['facebook_id'])) {
        header("Location: Profile.php?error=social_account_detected");
        exit();
    }

    // b. Required Check: Dapat may laman lahat
    if (empty($current_pass) || empty($new_pass) || empty($confirm_pass)) {
        header("Location: Profile.php?error=all_fields_required");
        exit();
    }

    // c. I-verify ang Current Password
    if (password_verify($current_pass, $row['password'])) {
        
        // d. Check kung nag-ma-match ang New at Confirm
        if ($new_pass === $confirm_pass) {
            
            // e. I-hash ang bagong password at i-update ang database
            $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
            $update_sql = "UPDATE create_acc SET password = '$hashed_password' WHERE id = '$user_id'";
            
            if (mysqli_query($conn, $update_sql)) {
                header("Location: Profile.php?status=password_updated");
            } else {
                header("Location: Profile.php?error=database_error");
            }
        } else {
            header("Location: Profile.php?error=password_mismatch");
        }
    } else {
        header("Location: Profile.php?error=incorrect_current_password");
    }
    exit();
}

// --- 3. MAIN FORM SUBMISSION (Confirm Photo & Text Info) ---
if (isset($_POST['btn_save_profile']) || isset($_POST['full_name'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact_number']);
    $home_addr = mysqli_real_escape_string($conn, $_POST['address_home']);
    $work_addr = mysqli_real_escape_string($conn, $_POST['address_work']);

    // MGA IDINAGDAG: Kunin ang coordinates mula sa POST
    $lat_home = isset($_POST['lat_home']) ? mysqli_real_escape_string($conn, $_POST['lat_home']) : '';
    $lng_home = isset($_POST['lng_home']) ? mysqli_real_escape_string($conn, $_POST['lng_home']) : '';
    $lat_work = isset($_POST['lat_work']) ? mysqli_real_escape_string($conn, $_POST['lat_work']) : '';
    $lng_work = isset($_POST['lng_work']) ? mysqli_real_escape_string($conn, $_POST['lng_work']) : '';

    // Check for uploads
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $file = null;
    if (!empty($_FILES['profile_pic']['name'])) $file = $_FILES['profile_pic'];
    elseif (!empty($_FILES['profile_pic_camera']['name'])) $file = $_FILES['profile_pic_camera'];

    if ($file) {
        $target = $upload_dir . time() . "_" . $file['name'];
        if (move_uploaded_file($file['tmp_name'], $target)) {
            mysqli_query($conn, "UPDATE create_acc SET profile_pic = '$target' WHERE id = '$user_id'");
        }
    }

    // IDINAGDAG: Isama ang coordinates sa SQL Update query
    $sql = "UPDATE create_acc SET 
            full_name = '$full_name', 
            contact_number = '$contact', 
            address_home = '$home_addr', 
            address_work = '$work_addr',
            lat_home = '$lat_home',
            lng_home = '$lng_home',
            lat_work = '$lat_work',
            lng_work = '$lng_work'
            WHERE id = '$user_id'";
            
    mysqli_query($conn, $sql);
    header("Location: Profile.php?status=success");
}
?>