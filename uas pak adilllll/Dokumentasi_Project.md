# Dokumentasi Sistem Manajemen Event
## Ujian Akhir Semester (UAS)

### 1. Pendahuluan
Aplikasi ini adalah **Sistem Manajemen Event Kampus** berbasis Web (Smart Event Campus) yang memungkinkan pengguna untuk melakukan pendaftaran pada suatu event secara online. Terdapat dua role utama dalam sistem ini:
- **Pengguna Umum (User)**: Dapat melihat daftar event yang tersedia, melihat detail lengkap artikel acara, mendaftar ke event, mengunduh poster, dan berbagi informasi acara ke WhatsApp.
- **Administrator (Admin)**: Memiliki hak akses penuh untuk mengelola (CRUD) data event, melihat daftar pendaftar, mengunduh laporan dalam bentuk PDF, serta menjawab pertanyaan pengguna melalui fitur Live Support.

### 2. Struktur Database
Sistem ini menggunakan database MySQL. Terdapat beberapa tabel utama yang digunakan (berdasarkan `database.sql`):
1. **Tabel `users`**: Menyimpan data akun admin (Username, Password terenkripsi bcrypt, Role).
2. **Tabel `events`**: Menyimpan informasi rinci acara termasuk judul, deskripsi, tanggal, waktu, lokasi, jenis, status berbayar/gratis, harga, kuota, URL maps, dan file poster.
3. **Tabel `registrations`**: Menyimpan data peserta yang mendaftar ke acara, termasuk Ticket ID unik, nama, email, telepon, institusi, bukti pembayaran, dan status verifikasi.
4. **Tabel `cs_messages`**: Menyimpan seluruh riwayat percakapan antara pengguna dan admin Customer Service, dikelompokkan berdasarkan `cs_ticket_id`.

### 3. Penjelasan Fitur dan Antarmuka (Tampilan)

#### 3.1. Halaman Login (`login.php`)
Halaman login berfungsi sebagai gerbang masuk bagi Administrator. 
- **Fungsi**: Memvalidasi kredensial pengguna (username dan password).
- **Tampilan**: Form login "Secure Login" yang rapi dengan validasi input dan desain glassmorphism.

![Halaman Login](gambar/login.png)

#### 3.2. Halaman Utama / Beranda (`index.php`)
Halaman utama yang dapat diakses oleh publik.
- **Fungsi**: Menampilkan daftar event yang tersedia dengan fitur pencarian *live* (tanpa reload halaman) dan filter berdasarkan jenis kegiatan (Seminar, Workshop, Lomba, Pelatihan).
- **Navigasi**: Terdapat tombol **Jadwal Sholat** di navbar untuk mengakses halaman jadwal sholat, serta tombol **Admin Panel** untuk masuk ke dasbor admin.
- **Tampilan**: Daftar event dalam bentuk *card* premium dengan gambar poster, detail harga, tanggal, waktu, lokasi, dan tombol navigasi ke Detail Acara, Google Maps, dan Google Calendar.

![Halaman Utama](gambar/index.png)

#### 3.3. Halaman Detail Acara (`event_detail.php`)
Halaman artikel penuh untuk setiap acara dengan desain dua kolom (*Two-Column Layout*) yang elegan.
- **Unduh Poster**: Pengunjung bisa langsung mengunduh poster resmi acara.
- **Smart Share WhatsApp**: Membuat teks *copywriting* profesional otomatis lengkap dengan detail acara untuk disebarkan via WhatsApp.
- **Integrasi Kalender & Peta**: Tombol untuk menambah acara ke Google Calendar dan melihat lokasi via Google Maps.
- **Daftar Acara**: Tombol pendaftaran langsung yang mengarah ke form registrasi.
- **Hubungi CS**: Tautan ke halaman bantuan Customer Service.

#### 3.4. Halaman Dashboard Admin (`admin.php`)
Halaman ini adalah pusat kontrol untuk Administrator (Admin Panel).
- **Fungsi**: Menampilkan seluruh statistik event (Total Kegiatan, Total Pendaftar, dll) dalam bentuk visual/grafik.
- **Tampilan**: Terdapat ringkasan data kegiatan, grafik distribusi jenis kegiatan, dan daftar kelola kegiatan di bagian bawah.

![Dashboard Admin Atas](gambar/admin1.png)
![Dashboard Admin Bawah](gambar/admin2.png)

#### 3.5. Halaman Kelola Event (`create_event.php` & `edit_event.php`)
Halaman ini digunakan oleh Admin untuk mengelola data event secara detail.
- **Fungsi**: Memasukkan rincian event baru atau mengubah rincian event yang sudah ada seperti Judul Kegiatan, Jenis Kegiatan, Lokasi, URL Maps, status berbayar/gratis, harga, kuota, dan **upload gambar poster**.
- **Tampilan**: Form input data event yang lengkap dengan antarmuka yang bersih.

![Form Tambah Event](gambar/create.png)
![Form Edit Event](gambar/edit.png)

#### 3.6. Halaman Pendaftaran Event (`register_event.php`)
Halaman yang digunakan oleh peserta/pengguna umum untuk mendaftar pada suatu event.
- **Fungsi**: Mengisi form pendaftaran acara (Nama Lengkap, Email, Telepon, Institusi, dan Upload Bukti Pembayaran untuk event berbayar). Sistem secara otomatis membuat **Ticket ID** unik.
- **Tampilan**: Form pendaftaran yang interaktif dengan ringkasan event di bagian atas.

![Form Registrasi](gambar/register.png)

#### 3.7. Laporan dan Ekspor PDF (`export_pdf.php` & `export_registrations_pdf.php`)
Fitur penting bagi Admin untuk mencetak laporan resmi.
- **Fungsi**: Menghasilkan dokumen cetak berisi daftar seluruh kegiatan atau daftar pendaftar pada suatu event tertentu.
- **Tampilan**: Halaman siap cetak (Print Preview) yang rapi, berlogo Smart Event Campus, dan berisi tabel data lengkap.

![Cetak Laporan Kegiatan](gambar/laporan_kegiatan.png)
![Cetak Laporan Pendaftar](gambar/laporan_pendaftar.png)

### 4. Fitur-Fitur Unggulan Tambahan (Pembaruan Terbaru)

Sistem ini telah ditingkatkan dengan tambahan fitur premium untuk memberikan pengalaman pengguna yang lebih baik:

#### 4.1. Sistem Bantuan Customer Service (`bantuan_support.php` & `admin_support.php`)
Sistem dilengkapi dengan fitur dukungan pelanggan (*Customer Support*) berbasis Ticket System.
- **User Side**: Tersedia *Floating Button* (tombol melayang) di sudut kanan bawah setiap halaman. Pengguna memasukkan nama dan sistem secara otomatis membuat **Ticket ID** unik. Pengguna bisa menyimpan Ticket ID untuk melanjutkan percakapan di waktu lain.
- **Admin Side**: Admin memiliki panel khusus (`admin_support.php`) untuk melihat seluruh percakapan dari semua pengguna dan membalas pesan.
- **Catatan Deployment**: Nama file menggunakan istilah `support` (bukan `chat`) untuk menghindari pemblokiran otomatis oleh sistem keamanan *shared hosting*.

#### 4.2. Halaman Jadwal Waktu Sholat (`jadwal_sholat.php`)
Fitur unik yang terintegrasi langsung dengan API eksternal terpercaya.
- **Aladhan API (Method 11)**: Menggunakan API dari [api.aladhan.com](https://aladhan.com) dengan metode kalkulasi nomor 11.
- **Izin Lokasi Presisi**: Sistem meminta izin GPS presisi dari browser pengguna (`enableHighAccuracy: true`).
- **Reverse Geocoding**: Menggunakan API gratis dari *OpenStreetMap Nominatim* untuk mengubah koordinat menjadi nama Kota/Kabupaten yang bisa dibaca.
- **Penambahan Waktu +3 Menit**: Seluruh waktu sholat (Subuh, Dzuhur, Ashar, Maghrib, Isya) secara otomatis ditambah 3 menit sebagai waktu kehati-hatian (*Ihtiyat*).
- **Informasi Lengkap**: Menampilkan nama lokasi (Kota, Provinsi) dan koordinat presisi (Latitude & Longitude 5 desimal).

#### 4.3. Desain Responsif & Glassmorphism Premium (Full Dark Mode)
Antarmuka pengguna dirombak total menggunakan CSS modern (Glassmorphism, animasi hover, gradien elegan) dengan skema *Full Dark Mode*. Seluruh elemen dipastikan **responsif di semua perangkat** dari Desktop (1920px) hingga Smartphone terkecil (360px). Fitur *Hamburger Menu* (Garis Tiga) kini diterapkan secara global untuk semua ukuran layar agar tampilan navbar selalu bersih.

#### 4.4. Live Search & Filter Event
Fitur pencarian dan filter di halaman utama menggunakan JavaScript `fetch` API sehingga hasil pencarian muncul secara *real-time* tanpa perlu me-reload halaman, memberikan pengalaman pengguna yang jauh lebih cepat dan modern.

#### 4.5. Pembaruan dan Penyempurnaan Sistem (Patch Terbaru)
Beberapa pembaruan terbaru yang telah diimplementasikan:
- **Fitur Unduh Materi**: Penambahan tombol "Unduh Materi" pada halaman utama (`index.php`) dan hasil pencarian (*Live Search*) apabila Admin telah mengunggah file materi (`material_file`) saat membuat atau mengedit acara.
- **Perbaikan Bug Edit Event**: Memperbaiki *Fatal Error: ArgumentCountError* pada `edit_event.php` dengan menyamakan jumlah tipe parameter (*bind_param*) dan jumlah variabel secara dinamis.
- **Peningkatan Keterbacaan Tabel Admin**: Menyesuaikan skema warna pada halaman kelola pendaftar (`admin_registrations.php`), mengubah warna latar tombol aksi (Verifikasi/Tolak/Kehadiran) serta judul kolom tabel menjadi kontras (putih) agar mudah dibaca pada latar belakang gelap.
- **Mekanisme Cache Buster**: Menambahkan parameter `?v=time()` pada pemanggilan file `style.css` di seluruh halaman `.php` untuk memastikan browser pengguna selalu memuat versi desain CSS terbaru tanpa mengalami isu *cache*.

### 5. Panduan Instalasi dan Konfigurasi

#### Instalasi Lokal (XAMPP / Laragon)
1. Pindahkan folder project (`uas pak adil`) ke dalam folder `www` (Laragon) atau `htdocs` (XAMPP).
2. Buat database baru di MySQL/phpMyAdmin (misalnya `smart_event_campus`).
3. Import file `database.sql` ke dalam database yang baru dibuat.
4. Sesuaikan konfigurasi database pada file `config.php` (host, username, password, dan nama database).
5. Buka browser dan akses `http://localhost/uas pak adil`.

#### Instalasi Hosting (InfinityFree / Shared Hosting)
1. Buat database baru dari *Control Panel* hosting (nama database sudah otomatis diberi awalan).
2. Import `database.sql` **langsung** ke dalam database tersebut via phpMyAdmin (file sudah tidak mengandung perintah `CREATE DATABASE` yang dilarang hosting).
3. Unggah seluruh file project ke folder `htdocs` di *File Manager* hosting.
4. Sesuaikan konfigurasi pada `config.php` dengan kredensial database yang diberikan oleh hosting.

**Akun Admin Default:**
- Username: `admin`
- Password: `admin123`

### 6. Kesimpulan
Sistem Manajemen Event Kampus "Smart Event Campus" ini dibangun menggunakan arsitektur PHP native yang terstruktur dipadukan dengan desain antarmuka modern yang premium dan interaktif. Dengan kelengkapan fitur manajemen data, cetak laporan PDF, sistem tiket Customer Support, jadwal sholat terintegrasi API, pencarian real-time, serta kompatibilitas penuh terhadap semua ukuran layar (responsif), aplikasi ini sangat siap digunakan untuk menangani siklus acara publik maupun internal kampus secara profesional.
