<?php
session_start(); // Simulan ang session para ma-access ang mga variables

// 1. Burahin lahat ng session variables
session_unset(); 

// 2. I-destroy ang session mismo
session_destroy(); 

// 3. I-redirect ang user pabalik sa Login page o Landing page
header("Location: index.php"); // Palitan ang login.html kung ano ang pangalan ng login file mo
exit();
?>