<?php
// Database configuration
$servername = "sql107.infinityfree.com";
$username = "if0_39091853";
$password = "7robHDN18l1"; // set your MySQL root password here
$dbname = "if0_39091853_student_management";


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

