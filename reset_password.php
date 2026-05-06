<?php
date_default_timezone_set('Asia/Manila'); // Fix para mag-match ang oras sa forgot_password.php[cite: 4, 5]
session_start();
include "db_conn.php"; // Koneksyon sa database[cite: 1, 2, 5, 9]

$message = "";
$message_type = "";
$token_valid = false;
$token = $_GET['token'] ?? '';

if (empty($token)) {
    die("Error: Invalid access. Walang token na nahanap.");
}

// 1. I-verify kung ang token ay valid at hindi pa expired[cite: 9]
$sql = "SELECT * FROM password_resets WHERE token = '$token' AND expiry > NOW()";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $token_valid = true;
    $reset_data = mysqli_fetch_assoc($result);
    $email = $reset_data['email'];
} else {
    $message = "Paumanhin, ang link na ito ay expired na o hindi valid. Pakisubukang muli.";
    $message_type = "error";
}

// 2. Pag-handle sa Form Submission (New Password)[cite: 9]
if ($_SERVER["REQUEST_METHOD"] == "POST" && $token_valid) {
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $message = "Hindi magkatugma ang password. Pakicheck muli.";
        $message_type = "error";
    } else {
        // I-update ang password sa 'create_acc' gamit ang hashing para sa security[cite: 2, 5, 9]
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE create_acc SET password = '$hashed_password' WHERE email = '$email'";
        
        if (mysqli_query($conn, $update_sql)) {
            // Burahin na ang token para hindi na muling magamit[cite: 9]
            mysqli_query($conn, "DELETE FROM password_resets WHERE email = '$email'");
            
            $message = "Tagumpay! Nabago na ang iyong password. Maaari ka na ngayong mag-login.";
            $message_type = "success";
            $token_valid = false; // Itago ang form[cite: 5, 9]
        } else {
            $message = "Nagkaroon ng problema sa pag-update. Subukan muli.";
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Kainan ni Ate Kabayan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary-color: #F4A42B; --bg-color: #FFF8E7; --text-color: #000; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--bg-color); display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .reset-container { background: #fff; padding: 40px; border-radius: 20px; border: 2px solid var(--text-color); box-shadow: 10px 10px 0px var(--text-color); width: 90%; max-width: 400px; text-align: center; }
        h2 { font-family: 'Fredoka One', cursive; color: var(--text-color); margin-bottom: 10px; font-size: 1.8rem; }
        .input-group { text-align: left; margin-bottom: 20px; }
        .input-group label { font-weight: 800; font-size: 0.85rem; display: block; margin-bottom: 8px; }
        .input-group input { width: 100%; padding: 15px; border: 2px solid var(--text-color); border-radius: 12px; box-sizing: border-box; outline: none; }
        .btn-submit { width: 100%; padding: 15px; background-color: var(--primary-color); color: white; border: 2px solid var(--text-color); border-radius: 12px; font-weight: 800; cursor: pointer; text-transform: uppercase; }
        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; font-size: 0.85rem; font-weight: 600; border: 2px solid var(--text-color); }
        .alert-success { background-color: #d4edda; color: #155724; }
        .alert-error { background-color: #f8d7da; color: #721c24; }
        .login-link { display: block; margin-top: 20px; color: var(--text-color); text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>

<div class="reset-container">
    <h2>Bagong Password</h2>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if ($token_valid): ?>
        <form action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>" method="POST">
            <div class="input-group">
                <label>New Password</label>
                <input type="password" name="password" required placeholder="Min. 6 characters">
            </div>
            <div class="input-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" required placeholder="Repeat password">
            </div>
            <button type="submit" class="btn-submit">I-save ang Password</button>
        </form>
    <?php else: ?>
        <a href="forgot_password.php" class="login-link">Humiling ng bagong link</a>
    <?php endif; ?>

    <a href="login.html" class="login-link"><i class="fa-solid fa-arrow-left"></i> Balik sa Login</a>
</div>

</body>
</html>