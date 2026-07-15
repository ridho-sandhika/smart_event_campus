-- Fix: Drop tabel lama dan buat ulang dengan struktur yang benar
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `event_reviews`;
DROP TABLE IF EXISTS `registrations`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `announcements`;
DROP TABLE IF EXISTS `news`;
DROP TABLE IF EXISTS `cs_messages`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. Tabel users
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin') DEFAULT 'admin',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Password: admin123
INSERT INTO `users` (`username`, `password`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uSsed1y0W2', 'admin');

-- 2. Tabel events
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `location` varchar(255) NOT NULL,
  `maps_url` varchar(500) DEFAULT NULL,
  `event_type` enum('Seminar','Workshop','Lomba','Pelatihan') NOT NULL,
  `is_paid` tinyint(1) DEFAULT 0,
  `price` decimal(10,2) DEFAULT 0.00,
  `max_participants` int(11) DEFAULT NULL,
  `poster` varchar(255) DEFAULT NULL,
  `material_file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample data events
INSERT INTO `events` (`title`, `description`, `event_date`, `event_time`, `location`, `maps_url`, `event_type`, `is_paid`, `price`, `max_participants`) VALUES
('Workshop Full-Stack Web Development dengan Laravel & Vue.js', 'Workshop intensif selama 2 hari untuk mempelajari pengembangan web modern menggunakan Laravel sebagai backend dan Vue.js sebagai frontend. Peserta akan membuat aplikasi nyata dari nol.', '2026-07-22', '09:00:00', 'Lab Komputer Gedung F Lt. 2 - Ruang F201', NULL, 'Workshop', 1, 150000.00, 50),
('Pelatihan Sertifikasi Microsoft Office Specialist (MOS) 2025', 'Pelatihan intensif untuk mempersiapkan peserta menghadapi ujian sertifikasi Microsoft Office Specialist. Mencakup Word, Excel, dan PowerPoint tingkat Expert.', '2026-07-25', '13:00:00', 'Lab Komputer Terpadu Gedung C Lt. 1 - Ruang C102', NULL, 'Pelatihan', 1, 200000.00, 30),
('Seminar Nasional Kecerdasan Buatan & Machine Learning 2025', 'Seminar nasional menghadirkan pakar AI dari industri dan akademisi. Topik meliputi Deep Learning, Computer Vision, NLP, dan implementasi AI di berbagai sektor.', '2026-07-29', '08:00:00', 'Auditorium Utama Gedung Rektorat Lt. 3', NULL, 'Seminar', 0, 0.00, 300),
('Lomba Desain UI/UX Nasional 2025', 'Kompetisi desain antarmuka pengguna tingkat nasional. Peserta akan merancang aplikasi mobile untuk kategori Kesehatan, Pendidikan, atau Lingkungan.', '2026-08-05', '08:00:00', 'Gedung Kreatif Digital Lt. 4', NULL, 'Lomba', 0, 0.00, 100),
('Pelatihan Public Speaking & Presentasi Profesional', 'Pelatihan intensif meningkatkan kemampuan berbicara di depan umum dan membuat presentasi yang memukau. Dibimbing oleh trainer bersertifikat internasional.', '2026-08-10', '09:00:00', 'Aula Serbaguna Gedung Student Center', NULL, 'Pelatihan', 1, 75000.00, 40);

-- 3. Tabel registrations
CREATE TABLE `registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `ticket_id` varchar(50) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `institution` varchar(100) NOT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `status` enum('pending','verified','rejected') DEFAULT 'pending',
  `attendance_status` enum('absent','present') DEFAULT 'absent',
  `registered_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket_id` (`ticket_id`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Tabel announcements
CREATE TABLE `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `content` text NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `announcements` (`title`, `content`, `is_active`) VALUES
('Pendaftaran Workshop Laravel & Vue.js Dibuka!', 'Segera daftarkan diri Anda untuk Workshop Full-Stack Web Development. Tempat terbatas hanya 50 peserta!', 1),
('Beasiswa Peserta Seminar AI', 'Tersedia 20 kursi gratis untuk mahasiswa berprestasi pada Seminar Nasional Kecerdasan Buatan. Hubungi BEM untuk info lebih lanjut.', 1);

-- 5. Tabel news
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `news` (`title`, `content`) VALUES
('Kampus Raih Penghargaan Inovasi Digital Terbaik 2025', 'Universitas kita berhasil meraih penghargaan bergengsi sebagai kampus dengan inovasi digital terbaik di tingkat nasional. Penghargaan ini diberikan atas berbagai program digitalisasi layanan mahasiswa.'),
('Program Magang Industri Dibuka untuk Semester Genap', 'Pusat Karir membuka pendaftaran program magang industri di 50+ perusahaan mitra. Mahasiswa semester 5 ke atas dapat mendaftar melalui portal mahasiswa.');

-- 6. Tabel event_reviews
CREATE TABLE `event_reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `reviewer_name` varchar(100) NOT NULL,
  `rating` int(1) NOT NULL,
  `comment` text,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 7. Tabel cs_messages
CREATE TABLE `cs_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cs_ticket_id` varchar(50) NOT NULL,
  `user_name` varchar(100) NULL,
  `sender_type` enum('user','admin') NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `cs_ticket_id` (`cs_ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
