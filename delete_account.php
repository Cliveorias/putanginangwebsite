<?php
// 1. Simulan ang session para makuha ang user_id
session_start();

// 2. Isama ang database connection
include "db_conn.php";

// 3. I-enable ang error reporting (Pansamantala lang para makita natin kung may error sa SQL)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 4. Check kung may naka-login na user
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // 5. SQL Query para burahin ang account
    $sql = "DELETE FROM create_acc WHERE id = '$user_id'";

    if (mysqli_query($conn, $sql)) {
        // 6. Kapag success ang pagbura, linisin ang session (Logout)
        session_unset();
        session_destroy();

        // 7. MAG-OUTPUT NG SCRIPT PARA HINDI MAG-WHITE SCREEN
        echo "<script>
                alert('Your account has been permanently deleted. We are sorry to see you go.');
                window.location.href = 'login.html'; 
              </script>";
        exit(); // Mahalaga ito para tumigil ang script dito
    } else {
        // Kung may error sa SQL, ipakita para ma-debug natin
        die("Error deleting account: " . mysqli_error($conn));
    }
} else {
    // Kung walang session (hindi naka-login), balik sa login page
    header("Location: login.html");
    exit();
}
?>