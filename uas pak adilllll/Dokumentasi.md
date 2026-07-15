# Dokumentasi Sistem "Smart Event Campus"

## 1. Deskripsi Proyek
**Smart Event Campus** adalah sebuah aplikasi web yang dirancang untuk universitas guna menampilkan informasi kegiatan kampus seperti seminar, workshop, lomba, dan pelatihan mahasiswa. Aplikasi ini memiliki dua sisi, yaitu halaman publik (Front-end) untuk mahasiswa/umum dan halaman pengelola (Back-end/Admin) untuk administrator mencatat dan mengubah data.

## 2. Struktur Database
Database yang digunakan adalah `smart_event_campus` yang memiliki dua tabel:
- **Tabel `users`**: Menyimpan data admin yang bisa login.
  - `id` (INT, Primary Key, Auto Increment)
  - `username` (VARCHAR 50)
  - `password` (VARCHAR 255) (Disimpan dalam bentuk hash menggunakan `password_hash()` PHP)
- **Tabel `events`**: Menyimpan detail kegiatan kampus.
  - `id` (INT, Primary Key, Auto Increment)
  - `title` (VARCHAR 255)
  - `description` (TEXT)
  - `event_date` (DATE)
  - `event_time` (TIME)
  - `event_type` (ENUM: 'Seminar', 'Workshop', 'Lomba', 'Pelatihan')
  - `location` (VARCHAR 255)
  - `created_at` (TIMESTAMP)

## 3. Fitur Utama
1. **Public Event Listing (`index.php`)**: Menampilkan kegiatan kampus dalam bentuk grid card modern. Data diurutkan berdasarkan tanggal terdekat.
2. **Admin Login (`login.php`)**: Mengamankan dashboard admin menggunakan session.
3. **Admin Dashboard (`admin.php`)**: Menampilkan tabel seluruh kegiatan yang telah dibuat.
4. **CRUD Events**:
   - Create (`create_event.php`): Form tambah kegiatan.
   - Update (`edit_event.php`): Form edit kegiatan yang sudah ada.
   - Delete (`delete_event.php`): Proses hapus kegiatan dari database.
5. **Modern UI/UX (`style.css`)**: Menggunakan Vanilla CSS dengan desain responsif, variasi warna biru (Primary) dan layout berbasis Flexbox/Grid.

## 4. Panduan Pengujian (Persiapan Screenshot)
Untuk melengkapi laporan tugas, silakan ikuti skenario pengujian berikut dan ambil **Screenshot** pada masing-masing langkah:

1. **Pengujian Halaman Utama (Index)**
   - Buka `http://localhost/uas pak adil/index.php`.
   - Ambil screenshot yang menampilkan judul "Selamat Datang di Smart Event Campus" dan daftar kegiatan di bawahnya.
2. **Pengujian Login**
   - Buka halaman login (`http://localhost/uas pak adil/login.php`).
   - Masukkan Username: `admin` dan Password: `password123`.
   - Ambil screenshot form login yang telah diisi sebelum diklik.
3. **Pengujian Dashboard Admin**
   - Setelah berhasil login, Anda akan diarahkan ke `admin.php`.
   - Ambil screenshot yang menunjukkan tabel berisi data kegiatan, nama admin, dan tombol aksi.
4. **Pengujian Tambah Kegiatan**
   - Klik tombol "Tambah Kegiatan".
   - Isi form (contoh: Lomba Poster Digital).
   - Ambil screenshot saat form terisi.
   - Klik Simpan, lalu ambil screenshot alert hijau "Kegiatan berhasil ditambahkan" di halaman admin.
5. **Pengujian Edit Kegiatan**
   - Klik ikon Pensil (Edit) pada salah satu kegiatan.
   - Ubah tanggal atau lokasi.
   - Ambil screenshot.
6. **Pengujian Hapus Kegiatan**
   - Klik ikon Tempat Sampah (Delete).
   - Akan muncul alert konfirmasi. Ambil screenshot pop-up ini.

## 5. Cara Export ke PDF
Untuk memenuhi syarat pengumpulan **Dokumentasi PDF**, Anda dapat membuka file Markdown ini di VS Code, kemudian menggunakan ekstensi seperti **Markdown PDF** (oleh yzane), atau membuka file ini di browser (menggunakan ekstensi viewer Markdown) dan mencetaknya (`Ctrl+P` -> Save as PDF).

## 6. Persiapan Link GitHub
1. Buka folder proyek ini (`c:\Users\User\Documents\uas pak adil`) di Terminal/Command Prompt.
2. Jalankan perintah:
   ```bash
   git init
   git add .
   git commit -m "Initial commit Smart Event Campus"
   ```
3. Buat repositori baru di GitHub Anda.
4. Copy perintah yang diberikan GitHub untuk menambahkan remote origin dan push.
   ```bash
   git remote add origin https://github.com/username-anda/smart-event-campus.git
   git branch -M main
   git push -u origin main
   ```
5. Salin link repositori tersebut untuk dikumpulkan.
