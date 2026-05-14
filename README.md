Explore api routes

Read [](file:///c%3A/laragon/www/lofo-api/routes/api.php)

Optimized tool selection

Read [](file:///c%3A/laragon/www/lofo-api/routes/api.php)

Berdasarkan struktur folder dan rute yang ada di dalam api.php Anda, ini adalah versi README.md yang sudah disesuaikan secara detail dengan rute, controller, dan kemampuan API yang ada di *source code* Anda.

```markdown
<p align="center">
  <a href="#" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo" />
  </a>
</p>
<h1 align="center">📦 Lofo API</h1> <p align="center"> Backend REST API yang dibangun menggunakan <b>Laravel</b> untuk aplikasi mobile <b>Lofo</b>. </p>

## 🚀 Gambaran Umum

Lofo API adalah layanan backend yang dirancang untuk aplikasi mobile yang memungkinkan pengguna untuk:

- 🔐 **Melakukan autentikasi dengan aman** (login/registrasi & logout) berbasis token Sanctum.
- 👤 **Manajemen Profil** pengguna.
- 🏷️ **Membuat dan mengelola barang (Items)** yang diunggah pengguna.
- 📍 **Melacak Lokasi Barang** (update lokasi terakhir barang ditemukan).
- 📢 **Membuat dan mengelola Pengumuman (Announcements)**.

## 🧩 Teknologi yang Digunakan

- **Framework:** Laravel
- **ORM:** Eloquent
- **Database:** Relasional (MySQL / PostgreSQL sesuai `.env`)
- **Bahasa:** PHP
- **Autentikasi:** Laravel Sanctum (Token-based)

## 🛠️ Langkah Instalasi

1. **Clone repository dan masuk ke direktori proyek**
   ```bash
   git clone <repo-url>
   cd lofo-api
   ```

2. **Instal dependensi PHP**
   ```bash
   composer install
   ```

3. **Konfigurasi variabel lingkungan**
   Salin file `.env.example` ke `.env` dan sesuaikan pengaturan database Anda:
   ```bash
   cp .env.example .env
   ```

4. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

5. **Setup Database (Migrasi & Seeder)**
   Jalankan migrasi untuk membangun tabel-table seperti *Users*, *Profiles*, *Items*, *Announcements*, *ItemLocations*.
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

## 🚀 Menjalankan Proyek

Mulai development server bawaan Laravel:

```bash
php artisan serve
```

## 📘 Struktur API (Endpoints)

Semua rute dilindungi oleh Middleware `auth:sanctum` **kecuali** Login & Registrasi. Berikut adalah titik akses Endpoint yang tersedia.

### 🔐 Publik (Tanpa Token)
* `POST /api/register` - Registrasi pengguna baru.
* `POST /api/login` - Login pengguna dan mendapatkan token.

### 🛡️ Privat (Membutuhkan Bearer Token)

**Akun & Profil (`UserController` & `AuthController`)**
* `GET /api/users/me` - Menampilkan info otentikasi saat ini.
* `GET /api/users/profile` - Menampilkan data profil user.
* `PATCH /api/users/profile` - Memperbarui profil user.
* `GET /api/users/items` - Menampilkan semua barang milik user tersebut.
* `GET /api/users/announcements` - Menampilkan pengumuman miliki user tersebut.
* `POST /api/logout` - Logout dan hapus token.

**Barang (`ItemController`)**
* `GET /api/items` - Melihat daftar semua barang.
* `POST /api/items` - Membuat laporan barang.
* `GET /api/items/{id}` - Menampilkan detail dari satu barang spesifik.
* `PATCH /api/items/{id}` - Memperbarui data barang.
* `DELETE /api/items/{id}` - Menghapus data barang.
* `PATCH /api/items/{id}/location` - Memperbarui lokasi terakhir dilihat dari barang tertentu.

**Pengumuman (`AnnouncementController`)**
* `GET /api/announcements` - Menampilkan semua pengumuman.
* `POST /api/announcements` - Membuat Pengumuman baru.
* `GET /api/announcements/pending` - Menampilkan pengumuman tipe pending.
* `GET /api/announcements/{id}` - Membaca detail pengumuman.
* `PATCH /api/announcements/{id}` - Memperbarui status / data pengumuman.
* `DELETE /api/announcements/{id}` - Menghapus pengumuman.

## 🧱 Struktur Direktori

Direktori berprioritas tinggi sesuai dengan standar codebase API:

```text
app/
 ┣ Exceptions/
 ┃  ┣ Handler.php               # Handler untuk format response Error API
 ┣ Http/
 ┃  ┣ Controllers/
 ┃     ┣ Api/                   # Controller Logika REST API yang tersentralisasi
 ┃        ┣ AnnouncementController.php
 ┃        ┣ AuthController.php
 ┃        ┣ ItemController.php
 ┃        ┣ UserController.php
 ┣ Models/                      # Model Tabel Database
 ┃  ┣ Announcement.php
 ┃  ┣ Item.php
 ┃  ┣ ItemLocation.php
 ┃  ┣ Profile.php
 ┃  ┣ User.php
routes/
 ┣ api.php                      # Kumpulan semua routing endpoint
```

<p align="center">Dibuat menggunakan Laravel</p>
