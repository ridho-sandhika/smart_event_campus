<?php
require 'config.php';

// Cek kolom yang sudah ada dulu
function columnExists($conn, $table, $column) {
    $r = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $r && $r->num_rows > 0;
}

$success = [];
$errors = [];

// Tambah kolom is_paid
if (!columnExists($conn, 'events', 'is_paid')) {
    if ($conn->query("ALTER TABLE events ADD COLUMN is_paid TINYINT(1) NOT NULL DEFAULT 0 AFTER maps_url"))
        $success[] = "Kolom is_paid ditambahkan";
    else $errors[] = $conn->error;
} else { $success[] = "Kolom is_paid sudah ada"; }

// Tambah kolom price
if (!columnExists($conn, 'events', 'price')) {
    if ($conn->query("ALTER TABLE events ADD COLUMN price DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER is_paid"))
        $success[] = "Kolom price ditambahkan";
    else $errors[] = $conn->error;
} else { $success[] = "Kolom price sudah ada"; }

// Tambah kolom max_participants
if (!columnExists($conn, 'events', 'max_participants')) {
    if ($conn->query("ALTER TABLE events ADD COLUMN max_participants INT NOT NULL DEFAULT 0 AFTER price"))
        $success[] = "Kolom max_participants ditambahkan";
    else $errors[] = $conn->error;
} else { $success[] = "Kolom max_participants sudah ada"; }

// Buat tabel registrations
$tableCheck = $conn->query("SHOW TABLES LIKE 'registrations'");
if ($tableCheck->num_rows == 0) {
    $createReg = "CREATE TABLE `registrations` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `event_id` INT(11) NOT NULL,
        `full_name` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) NOT NULL,
        `phone` VARCHAR(20) NOT NULL,
        `institution` VARCHAR(255) NOT NULL,
        `payment_proof` VARCHAR(500) DEFAULT NULL,
        `status` ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
        `registered_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `event_id` (`event_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    if ($conn->query($createReg))
        $success[] = "Tabel registrations dibuat";
    else $errors[] = $conn->error;
} else { $success[] = "Tabel registrations sudah ada"; }

echo "✅ Sukses: " . implode(', ', $success);
if (!empty($errors)) echo "\n⚠️ Error: " . implode(', ', $errors);
?>
