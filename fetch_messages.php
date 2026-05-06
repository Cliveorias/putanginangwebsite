<?php
session_start();
include "db_conn.php";

$user_id = 0;
$view_as = 'Customer'; // Default view

if (isset($_GET['customer_id'])) {
    // Kapag may customer_id sa URL, ibig sabihin Admin ang tumitingin
    $user_id = mysqli_real_escape_string($conn, $_GET['customer_id']);
    $view_as = 'Admin';
} elseif (isset($_SESSION['user_id'])) {
    // Kapag wala, ibig sabihin yung mismong customer ang naka-login
    $user_id = $_SESSION['user_id'];
    $view_as = 'Customer';
}

$sql = "SELECT * FROM chat_messages WHERE user_id = '$user_id' ORDER BY created_at ASC";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $msg = htmlspecialchars($row['message']);
        $time = date("h:i A", strtotime($row['created_at']));
        $sender = $row['sender_type'];

        // Logic: Kung ang sender ay kapareho ng viewer, nasa KANAN (flex-end). Kung hindi, nasa KALIWA.
        $alignment = ($sender == $view_as) ? 'flex-end' : 'flex-start';
        $bg_color = ($sender == $view_as) ? '#F4A42B' : '#e4e6eb';
        $text_color = ($sender == $view_as) ? 'white' : '#050505';
        $border_radius = ($sender == $view_as) ? '18px 18px 4px 18px' : '18px 18px 18px 4px';

        echo "<div style='align-self: $alignment; background: $bg_color; color: $text_color; padding: 8px 15px; border-radius: $border_radius; max-width: 70%; word-wrap: break-word; font-size: 0.95rem; margin-bottom: 2px;'>
                $msg
                <div style='font-size: 0.65rem; text-align: " . ($alignment == 'flex-end' ? 'right' : 'left') . "; margin-top: 4px; color: " . ($alignment == 'flex-end' ? 'rgba(255,255,255,0.8)' : '#65676B') . ";'>$time</div>
              </div>";
    }
} else {
    echo "<div style='text-align:center; color:#999; margin-top: 20px;'>Start a conversation with Ate Kabayan!</div>";
}
?>