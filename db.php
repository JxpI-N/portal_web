<?php
$servername = "localhost:3307";
$username = "root";
$password = "";
$dbname = "portal_fortuna";

$conn = new mysqli("localhost:3307", "root", $password, "portal_fortuna");
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>