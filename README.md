# 🎓 Smart Event Campus - Premium Edition

Sistem Informasi Pengelolaan Kegiatan Kampus (Seminar, Workshop, Lomba, dan Pelatihan) komprehensif menggunakan **PHP Native** dan **MySQL**. Aplikasi ini hadir dengan perombakan antarmuka pengguna (UI/UX) modern mengusung konsep **Premium Indigo-Violet Glassmorphism** yang sepenuhnya responsif di seluruh perangkat.

---

## ✨ Pembaruan Terbaru (Patch Update)

1. **Tema Premium (Indigo-Violet Glassmorphism)**: Tampilan antarmuka dirombak menggunakan skema warna premium (*indigo* dan *violet*), efek tembus pandang (kaca), bayangan halus, serta animasi transisi memukau.
2. **Mode Gelap / Terang (Dark & Light Mode Toggle)**: Pengguna dapat mengganti tema sistem secara leluasa hanya dengan menekan tombol (tersedia di *hamburger menu*). Semua elemen menyesuaikan warna dan kontras secara dinamis.
3. **Global Hamburger Menu Overlay**: Seluruh navigasi dioptimalkan menggunakan sistem menu "garis tiga" (*hamburger*) yang muncul sebagai *fullscreen overlay*. Terbebas dari bug layout pada layar Laptop maupun Mobile.
4. **Floating Notification Badges**: Pengumuman penting dari admin tidak lagi merusak ruang layout, namun ditampilkan elegan sebagai *badge* mengambang di sudut bawah yang saat ditekan akan muncul dalam mode layar penuh (fullscreen).
5. **Back Button (Tombol Kembali) yang Intuitif**: Pemisahan tombol kembali dari struktur *navbar* untuk kenyamanan bernavigasi ke halaman utama maupun dashboard admin.

---

## 🚀 Fitur Utama

### 👤 Pengguna Umum (Publik)
- **Daftar Kegiatan Real-Time**: Daftar event kampus dengan animasi *fade-in*, fitur *Live Search* dan filtering cerdas tanpa harus me-refresh halaman.
- **Pendaftaran Interaktif**: Form registrasi event (gratis dan berbayar) yang aman dilengkapi *upload* bukti transfer.
- **Unduh Materi & Ekstraksi Poster**: Mengunduh *file* materi (modul) secara langsung jika telah disediakan oleh admin.
- **Customer Support (Live Ticket)**: Menghubungi admin secara langsung melalui *Floating CS Icon* dengan sistem percakapan yang menyimpan histori.
- **Smart Integration**: Sinkronisasi acara dengan Google Calendar dan navigasi ke Google Maps.
- **Jadwal Waktu Sholat Terintegrasi GPS**: Menampilkan jadwal sholat sesuai lokasi presisi pengguna (*Aladhan API* & *OpenStreetMap*).

### 👑 Administrator (Admin)
- **Dashboard Statistik & Visualisasi**: Panel informatif yang merangkum jumlah kegiatan, pendaftar terbaru, serta grafik batang/pie interaktif.
- **Manajemen Acara Lengkap (CRUD)**:
  - **Create**: Tambah event (nama, harga, slot tiket, poster, materi).
  - **Read**: Tinjau list kegiatan.
  - **Update**: Edit informasi dan kelengkapan file.
  - **Delete**: Hapus event (terintegrasi *bulk delete* / hapus massal).
- **Manajemen Pendaftar Ekstensif**:
  - Verifikasi otomatis / manual status pembayaran ("Verified", "Pending", "Rejected").
  - Catat kehadiran fisik peserta (*attendance tracking*).
  - Integrasi pesan WhatsApp yang sudah diformat (*pre-filled WhatsApp message*).
- **Sistem Pengumuman Terpadu (Announcement)**: Admin dapat menambahkan pengumuman yang otomatis muncul pada layar pengunjung sebagai notifikasi darurat/penting.
- **Pusat Bantuan CS (Admin Side)**: Balas pertanyaan dan tiket masuk dari peserta.
- **Ekspor Laporan PDF**: Unduh *(export)* daftar kegiatan dan data pendaftar dalam bentuk PDF rapi yang siap cetak.

---

## 🛠️ Teknologi dan Arsitektur

- **Bahasa Pemrograman**: PHP Native (Bebas framework, performa kilat)
- **Database**: MySQL / MariaDB (Terstruktur menggunakan `mysqli`)
- **Frontend & Styling**:
  - HTML5 & Vanilla CSS3 (Sistem variabel CSS Modern)
  - JavaScript murni (DOM Manipulation, AJAX)
- **Ikonografi & Tipografi**:
  - FontAwesome 6 (Ikon modern & solid)
  - Google Fonts (Outfit, Inter)
- **API Eksternal**: Aladhan API, Nominatim (OpenStreetMap)

---

## 📂 Struktur Direktori Proyek

```text
Smart_Event_Campus/
│
├── config.php                 # Konfigurasi database utama
├── database.sql               # Skema database & data awalan
├── fix_all.sql                # (Opsional) Patch untuk memperbaiki struktur tabel (Jika error)
├── style.css                  # File CSS pusat (Glassmorphism & Responsif)
├── index.php                  # Halaman Beranda (Landing Page Publik)
├── admin.php                  # Dashboard Administrator Utama
├── login.php                  # Gerbang masuk Admin
├── logout.php                 # Modul pemutus sesi
├── generate_doc.py            # Script generator DOCX Python otomatis
│
├── [Modul Event]              # create_event.php, edit_event.php, event_detail.php, dll.
├── [Modul Registrasi]         # register_event.php, admin_registrations.php
├── [Modul Dukungan/CS]        # bantuan_support.php, admin_support.php
├── [Modul Ekstra]             # jadwal_sholat.php, admin_news.php, admin_announcements.php
├── [Modul Export PDF]         # export_pdf.php, export_registrations_pdf.php
│
└── uploads/                   # Folder penampung aset unggahan
    ├── posters/               # Poster acara
    ├── materials/             # File dokumen acara
    ├── news/                  # Gambar berita
    └── payments/              # Bukti pembayaran peserta
```

---

## ⚙️ Panduan Instalasi (Lokal)

1. **Persiapan Lingkungan**: Pastikan Anda telah menginstal XAMPP, Laragon, atau lingkungan *web server* lokal lainnya (Apache & MySQL).
2. **Clone & Tempatkan Folder**: Pindahkan *(clone/ekstrak)* repositori ini ke dalam direktori publik server Anda (cth: `htdocs/` pada XAMPP, atau `www/` pada Laragon).
3. **Konfigurasi Database**:
   - Buka `phpMyAdmin` atau *MySQL Client* lainnya.
   - Buat database baru (contoh: `smart_event_campus`).
   - *Import* file `database.sql` ke dalam database yang baru dibuat.
4. **Sambungkan Aplikasi**: Buka file `config.php` dengan teks editor. Sesuaikan detail login:
   ```php
   $host = "localhost";
   $username = "root"; // Sesuaikan dengan user Anda
   $password = ""; // Sesuaikan dengan password Anda
   $database = "smart_event_campus"; // Sesuaikan dengan nama DB
   ```
5. **Jalankan Aplikasi**: Akses aplikasi melalui *browser* Anda: 
   Contoh :👉 `http://localhost/folder-aplikasi` *(Ganti dengan nama folder ekstrasi Anda, cth: `Smart_Event_Campus`)*.

---

## 🔑 Akses Kredensial Bawaan (Default Admin)

Untuk mencoba *Back-End*, gunakan kredensial berikut pada halaman `login.php`:

- **Username**: `admin`
- **Password**: `admin123`

---

## 📚 Catatan Tambahan (Troubleshooting)

- **Masalah Folder Uploads**: Jika pengguna tidak dapat mengunggah (upload) bukti pembayaran atau poster, pastikan folder `uploads/` beserta isinya (`posters`, `materials`, `payments`, `news`) memiliki hak akses *Write* (Permission 755 atau 777).
- **Masalah Pembuatan Tabel SQL**: Jika terjadi *syntax error* pada MySQL versi lama saat mengimpor `database.sql`, Anda dapat mengimpor file sekunder yaitu `fix_all.sql` untuk menimpa skema tanpa masalah kompatibilitas.
- **Cache CSS Lama**: Desain mengusung teknik *Cache-Busting* (`style.css?v=<?php echo time(); ?>`), namun apabila desain tidak berubah di perangkat Anda, lakukan *Hard Reload* (Tekan `Ctrl` + `F5` di PC, atau hapus cache browser di HP).

---
*Dikembangkan secara khusus sebagai pemenuhan Ujian Akhir Semester dengan dedikasi pada performa (Speed) dan pengalaman pengguna (UI/UX).* 🚀
