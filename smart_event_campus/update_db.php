<?php
require_once 'config.php';

try {
    // Check if column exists first
    $checkCol = $conn->query("SHOW COLUMNS FROM `registrations` LIKE 'attendance_status'");
    if ($checkCol->num_rows == 0) {
        $conn->query("ALTER TABLE `registrations` ADD COLUMN `attendance_status` enum('absent','present') DEFAULT 'absent'");
    }

    $checkColMat = $conn->query("SHOW COLUMNS FROM `events` LIKE 'material_file'");
    if ($checkColMat->num_rows == 0) {
        $conn->query("ALTER TABLE `events` ADD COLUMN `material_file` varchar(255) DEFAULT NULL");
    }

    $sql1 = "CREATE TABLE IF NOT EXISTS `announcements` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(150) NOT NULL,
      `content` text NOT NULL,
      `is_active` tinyint(1) DEFAULT 1,
      `created_at` timestamp NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    $conn->query($sql1);

    $sql2 = "CREATE TABLE IF NOT EXISTS `news` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(150) NOT NULL,
      `content` text NOT NULL,
      `image` varchar(255) DEFAULT NULL,
      `created_at` timestamp NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    $conn->query($sql2);

    $sql3 = "CREATE TABLE IF NOT EXISTS `event_reviews` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `event_id` int(11) NOT NULL,
      `reviewer_name` varchar(100) NOT NULL,
      `rating` int(1) NOT NULL,
      `comment` text,
      `created_at` timestamp NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    $conn->query($sql3);

    echo "Database updated successfully with 7 new features tables/columns.";
} catch (Exception $e) {
    echo "Error updating database: " . $e->getMessage();
}
?>
