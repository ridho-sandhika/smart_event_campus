<?php
require 'config.php';

echo "<h2>Memperbaiki Database...</h2>";

// Coba tambahkan kolom maps_url
$sql = "ALTER TABLE `events` ADD `maps_url` VARCHAR(500) NULL AFTER `location`";

if ($conn->query($sql) === TRUE) {
    echo "<p style='color:green;'>✅ BERHASIL! Kolom 'maps_url' sukses ditambahkan ke tabel events.</p>";
} else {
    // Jika errornya karena kolom sudah ada, berarti aman
    if (strpos($conn->error, 'Duplicate column name') !== false) {
        echo "<p style='color:blue;'>ℹ️ Kolom 'maps_url' ternyata sudah ada. (Aman)</p>";
    } else {
        echo "<p style='color:red;'>❌ Gagal: " . $conn->error . "</p>";
    }
}

echo "<br><p>Sekarang silakan coba Simpan Kegiatan lagi di web Anda!</p>";
echo "<p style='color:orange;'><i>(Jangan lupa hapus file ini dari hosting setelah selesai)</i></p>";
?>
