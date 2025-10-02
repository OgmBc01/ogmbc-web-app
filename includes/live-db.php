<?php
$host = "localhost";
$username = "u545186277_root";
$password = "OgmBc@6449";
$database = "u545186277_ogmbc";

$connection = mysqli_connect($host, $username, $password, $database);

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}
echo "";
?>