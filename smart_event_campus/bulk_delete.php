<?php
session_start();
require_once 'config.php';

// Cek admin login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ids']) && !empty($_POST['type'])) {
    $ids = $_POST['ids'];
    $type = $_POST['type'];

    // Sanitize ids (hanya angka)
    $clean_ids = array_map('intval', $ids);
    $id_list = implode(',', $clean_ids);

    if ($type === 'registrations') {
        // Ambil data file bukti transfer untuk dihapus dari server (jika ada)
        $res = $conn->query("SELECT payment_proof FROM registrations WHERE id IN ($id_list) AND payment_proof IS NOT NULL");
        while($r = $res->fetch_assoc()) {
            $file = 'uploads/' . $r['payment_proof'];
            if(file_exists($file)) {
                unlink($file);
            }
        }
        
        $sql = "DELETE FROM registrations WHERE id IN ($id_list)";
        if ($conn->query($sql)) {
            $_SESSION['swal_success'] = count($clean_ids) . " data pendaftar berhasil dihapus permanen.";
        } else {
            $_SESSION['swal_error'] = "Gagal menghapus data pendaftar.";
        }
        header("Location: admin_registrations.php");
        
    } else if ($type === 'events') {
        // Hapus juga semua registrasi yang terkait dengan event-event ini
        // Pertama hapus filenya
        $res = $conn->query("SELECT payment_proof FROM registrations WHERE event_id IN ($id_list) AND payment_proof IS NOT NULL");
        while($r = $res->fetch_assoc()) {
            $file = 'uploads/' . $r['payment_proof'];
            if(file_exists($file)) {
                unlink($file);
            }
        }
        
        // Hapus dari database (cascade manual karena mungkin fk tidak cascade)
        $conn->query("DELETE FROM registrations WHERE event_id IN ($id_list)");
        
        // Hapus events
        $sql = "DELETE FROM events WHERE id IN ($id_list)";
        if ($conn->query($sql)) {
            $_SESSION['swal_success'] = count($clean_ids) . " kegiatan berhasil dihapus permanen.";
        } else {
            $_SESSION['swal_error'] = "Gagal menghapus kegiatan.";
        }
        header("Location: admin.php");
    }
} else {
    header("Location: admin.php");
}
exit;
?>
