<?php
$sname = "localhost";
$uname = "root";
$password = ""; 
$db_name = "test"; // Eto dapat ang nakalagay base sa screenshot mo

$conn = mysqli_connect($sname, $uname, $password, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
// echo "Connected successfully!"; 
?>