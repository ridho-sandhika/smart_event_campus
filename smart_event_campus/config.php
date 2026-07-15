<?php
// =====================================================
// Konfigurasi Database - Laragon Local
// =====================================================
$host     = "localhost";
$username = "root";
$password = ""; // Laragon default: kosong
$database = "smart_event_campus";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
