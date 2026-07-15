# Smart Event Campus 🎓

Sistem Informasi Pengelolaan Kegiatan Kampus (Seminar, Workshop, Lomba, dan Pelatihan) komprehensif menggunakan PHP Native dan MySQL. Dilengkapi dengan antarmuka **Full Dark Mode Glassmorphism** yang modern dan responsif.

## 🚀 Fitur Utama

### 👤 Untuk Pengguna Umum (Publik)
1. **Daftar Kegiatan Real-Time**: Melihat daftar kegiatan kampus dengan fitur *Live Search* dan filter instan tanpa *reload* halaman.
2. **Pendaftaran Online**: Melakukan registrasi acara (gratis maupun berbayar) yang dilengkapi dengan sistem unggah bukti pembayaran.
3. **Smart Share & Integrasi**:
   - Bagikan info acara via WhatsApp otomatis.
   - Sinkronisasi dengan Google Calendar.
   - Terintegrasi Google Maps untuk petunjuk lokasi acara.
4. **Unduh Materi & Poster**: Pengunjung dapat mengunduh materi acara dan poster resmi kegiatan secara langsung.
5. **Customer Support (Tiket)**: Mengirimkan pertanyaan kepada admin secara langsung menggunakan sistem tiket (Live Chat Support).
6. **Jadwal Waktu Sholat**: Menampilkan jadwal sholat realtime akurat dengan deteksi lokasi otomatis berdasarkan GPS pengguna.

### 👑 Untuk Administrator (Admin)
1. **Dashboard Statistik**: Panel kontrol dengan ringkasan jumlah kegiatan, pendaftar, dan grafik distribusi kegiatan.
2. **Manajemen Acara (CRUD)**: Menambah, mengubah, menghapus, serta melihat detail kegiatan kampus beserta fitur upload kelengkapan (Poster & Materi).
3. **Manajemen Pendaftar**: Menyetujui/menolak pendaftar berbayar, mencatat kehadiran (*attendance*), dan *bulk delete* (hapus massal).
4. **Ekspor Laporan PDF**: Unduh laporan lengkap daftar acara maupun daftar peserta pendaftar secara rapi berformat PDF.
5. **Pusat Bantuan CS**: Panel untuk membalas seluruh tiket obrolan/pertanyaan yang diajukan pengunjung.

## 🛠️ Teknologi yang Digunakan
- **Frontend**: HTML5, Vanilla CSS3 (*Dark Theme Glassmorphism Custom Design System*)
- **Backend**: PHP (Native)
- **Database**: MySQL/MariaDB
- **Ikon & Font**: FontAwesome 6, Google Fonts (Outfit)
- **API Eksternal**: Aladhan API (Jadwal Sholat), OpenStreetMap Nominatim API (Geocoding)

## ⚙️ Panduan Instalasi
1. Clone atau unduh repositori ini ke dalam direktori `htdocs` (jika menggunakan XAMPP) atau direktori root server lokal Anda (`www` untuk Laragon).
2. Buat database baru di MySQL dengan nama bebas (contoh: `smart_event_campus`).
3. Import file `database.sql` ke dalam database tersebut.
4. Sesuaikan konfigurasi koneksi database pada file `config.php`:
   ```php
   $host = "localhost";
   $username = "root"; // Username database anda
   $password = ""; // Password database anda
   $database = "smart_event_campus"; // Nama database anda
   ```
5. Akses aplikasi melalui browser web: `http://localhost/folder-aplikasi`

## 🔑 Akses Login
- **Akses Publik**: Buka halaman utama (`index.php`) untuk melihat daftar kegiatan.
- **Login Admin**: Klik tombol menu (garis tiga) pada navbar, lalu pilih "Admin Panel" atau akses langsung `login.php`.
- **Kredensial Default Admin**:
  - **Username**: `admin`
  - **Password**: `admin123`

## 📚 Dokumentasi Lebih Lanjut
Dokumentasi teknis lengkap dan panduan screenshot dapat ditemukan di dalam file `Dokumentasi_Project.md` dan juga dalam versi yang siap cetak `Dokumentasi_Project.docx`.
