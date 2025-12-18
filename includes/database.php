<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "ogmbc";

$connection = mysqli_connect($host, $username, $password, $database);

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}
 
// Database connection ready
// (Session timeout enforcement moved to admin functions for reuse)
?>