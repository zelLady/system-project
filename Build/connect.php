<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "shop1"; // Specify same name sa Database nga imong gihimo sa phpMyAdmin

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>