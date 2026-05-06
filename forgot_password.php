<?php
date_default_timezone_set('Asia/Manila'); // Fix para sa timezone mismatch[cite: 4, 5, 8]
session_start();
include "db_conn.php"; // Koneksyon sa database[cite: 1, 2, 4, 7, 8]

// I-include ang PHPMailer files (Siguraduhing tama ang path ng folder mo)
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // 1. I-verify kung registered ang email sa 'create_acc' table[cite: 4, 7, 8]
    $check_email = "SELECT * FROM create_acc WHERE email = '$email'";
    $result = mysqli_query($conn, $check_email);

    if (mysqli_num_rows($result) > 0) {
        $user_row = mysqli_fetch_assoc($result);

        // 2. I-verify kung ang account ay naka-link sa Social Login (Google o Facebook)
        // Kung may laman ang google_id o fb_id, hindi papayagan ang manual reset[cite: 3, 6]
        if (!empty($user_row['google_id']) || !empty($user_row['fb_id'])) {
            $message = "Kabayan, ang account na ito ay naka-link sa Google o Facebook. Mangyaring gamitin ang Social Login button sa login page.";
            $message_type = "error";
        } else {
            // 3. Gumawa ng Secure Token kung manual account ito[cite: 4, 7, 8]
            $token = bin2hex(random_bytes(32));
            $expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));

            // 4. I-save sa 'password_resets' table[cite: 4, 7, 8]
            $sql = "INSERT INTO password_resets (email, token, expiry) VALUES ('$email', '$token', '$expiry')";
            
            if (mysqli_query($conn, $sql)) {
                // --- START NG PHPMAILER LOGIC ---
                $mail = new PHPMailer(true);

                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'jtan70194@gmail.com'; // Iyong Gmail[cite: 7, 8]
                    $mail->Password   = 'iasimgnzklnreuyf';      // Ang iyong App Password[cite: 7, 8]
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    // Recipients
                    $mail->setFrom('no-reply@atekabayan.com', 'Kainan ni Ate Kabayan');
                    $mail->addAddress($email); 

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Password Reset Request - Kainan ni Ate Kabayan';
                    $mail->Body    = "
                        <div style='font-family: Arial, sans-serif; border: 2px solid #F4A42B; padding: 20px; border-radius: 15px;'>
                            <h2 style='color: #F4A42B;'>Kumusta, Kabayan!</h2>
                            <p>Nakatanggap kami ng request na i-reset ang iyong password. I-click ang button sa ibaba para magtuloy:</p>
                            <br>
                            <a href='http://localhost/updated/reset_password.php?token=$token' 
                               style='background: #F4A42B; color: white; padding: 12px 25px; text-decoration: none; border-radius: 10px; font-weight: bold; display: inline-block;'>
                               I-RESET ANG PASSWORD
                            </a>
                            <br><br>
                            <p style='font-size: 0.8rem; color: #777;'>Ang link na ito ay mag-eexpire matapos ang isang oras. Kung hindi mo ito hiling, maaari mong balewalain ang email na ito.</p>
                        </div>";

                    $mail->send();
                    $message = "Kabayan, nakagawa na kami ng reset link! I-check ang iyong email para sa susunod na hakbang.";
                    $message_type = "success";
                } catch (Exception $e) {
                    $message = "Hindi maipadala ang email. Error: {$mail->ErrorInfo}";
                    $message_type = "error";
                }
                // --- END NG PHPMAILER LOGIC ---
            } else {
                $message = "May mali sa system. Pakisubukang muli.";
                $message_type = "error";
            }
        }
    } else {
        $message = "Paumanhin, ang email na ito ay hindi registered sa ating system.";
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Kainan ni Ate Kabayan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #F4A42B;
            --bg-color: #FFF8E7;
            --text-color: #000;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .forgot-container {
            background: #fff;
            padding: 40px;
            border-radius: 20px;
            border: 2px solid var(--text-color);
            box-shadow: 10px 10px 0px var(--text-color);
            width: 90%;
            max-width: 400px;
            text-align: center;
        }

        .logo-circle {
            width: 80px;
            height: 80px;
            background: var(--primary-color);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--text-color);
            overflow: hidden;
        }

        .logo-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        h2 {
            font-family: 'Fredoka One', cursive;
            color: var(--text-color);
            margin-bottom: 10px;
            font-size: 1.8rem;
        }

        p {
            font-size: 0.9rem;
            color: #555;
            margin-bottom: 25px;
        }

        .input-group {
            text-align: left;
            margin-bottom: 20px;
        }

        .input-group label {
            font-weight: 800;
            font-size: 0.85rem;
            display: block;
            margin-bottom: 8px;
        }

        .input-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid var(--text-color);
            border-radius: 12px;
            box-sizing: border-box;
            font-family: inherit;
            outline: none;
        }

        .btn-reset {
            width: 100%;
            padding: 15px;
            background-color: var(--primary-color);
            color: white;
            border: 2px solid var(--text-color);
            border-radius: 12px;
            font-weight: 800;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
            text-transform: uppercase;
        }

        .btn-reset:hover {
            background-color: #e0931a;
            transform: translateY(-2px);
        }

        .back-link {
            display: block;
            margin-top: 20px;
            color: var(--text-color);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            border: 2px solid var(--text-color);
        }

        .alert-success { background-color: #d4edda; color: #155724; }
        .alert-error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<div class="forgot-container">
    <div class="logo-circle">
        <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772298452/logo_ate_kabayan_jtfqeg.jpg" alt="Logo">
    </div>
    
    <h2>Nakalimutan?</h2>
    <p>Huwag mag-alala, Kabayan! Ilagay lang ang iyong email at padadalhan ka namin ng reset link.</p>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form action="forgot_password.php" method="POST">
        <div class="input-group">
            <label>Registered Email</label>
            <input type="email" name="email" placeholder="e.g. juan@email.com" required>
        </div>
        
        <button type="submit" class="btn-reset">I-send ang Reset Link</button>
    </form>

    <a href="login.html" class="back-link"><i class="fa-solid fa-arrow-left"></i> Balik sa Login</a>
</div>

</body>
</html>