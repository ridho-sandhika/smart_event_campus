<?php
session_start();
require_once 'config.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    
    $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success_msg'] = "Kegiatan berhasil dihapus!";
    } else {
        $_SESSION['error_msg'] = "Gagal menghapus kegiatan: " . $conn->error;
    }
    
    $stmt->close();
} else {
    $_SESSION['error_msg'] = "ID kegiatan tidak valid!";
}

header("Location: admin.php");
exit;
?>
