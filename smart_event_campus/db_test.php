<?php
// File tes koneksi - HAPUS SETELAH SELESAI!
$host     = "sql107.infinityfree.com";
$username = "if0_42371472";
$password = "dn0CGwovxAOrXpo";
$database = "if0_42371472_smart_event_campus";

$conn = new mysqli($host, $username, $password, $database);

echo "<h2>Test Koneksi Database</h2>";

if ($conn->connect_error) {
    echo "<p style='color:red;'><b>❌ KONEKSI GAGAL!</b><br>Error: " . $conn->connect_error . "</p>";
    echo "<p>Kemungkinan penyebab:<br>- Password MySQL salah<br>- Database belum dibuat<br>- Host salah</p>";
} else {
    echo "<p style='color:green;'><b>✅ KONEKSI BERHASIL!</b></p>";
    
    // Cek tabel
    $tables = ['users', 'events', 'registrations'];
    echo "<h3>Status Tabel:</h3><ul>";
    foreach($tables as $table) {
        $res = $conn->query("SHOW TABLES LIKE '$table'");
        if($res && $res->num_rows > 0) {
            echo "<li style='color:green;'>✅ Tabel <b>$table</b> - Ada</li>";
        } else {
            echo "<li style='color:red;'>❌ Tabel <b>$table</b> - TIDAK ADA! (Perlu import database.sql)</li>";
        }
    }
    echo "</ul>";
    
    // Cek user admin
    $res = $conn->query("SELECT username FROM users WHERE username='admin'");
    if($res && $res->num_rows > 0) {
        echo "<p style='color:green;'>✅ Akun admin ditemukan di database.</p>";
    } else {
        echo "<p style='color:red;'>❌ Akun admin TIDAK ADA di database! Silakan import database.sql terlebih dahulu.</p>";
    }
}
echo "<br><p style='color:orange;'>⚠️ HAPUS FILE INI SETELAH SELESAI DIAGNOSA!</p>";
?>
