# 📢 LoFo (Lost & Found) - Android App & Backend API

Aplikasi pelaporan dan pelacakan barang hilang berbasis digital untuk lingkungan sekolah. Dikembangkan sebagai solusi modern menggantikan metode konvensional (grup pesan manual) dengan memanfaatkan teknologi **QR Code (Barcode Scan-to-Update)** dan **Integrasi Lokasi berbasis Koordinat Geografis**.

---

## 👥 Profil Kelompok & Pembagian Tugas (Kelompok 5)
Proyek ini disusun oleh siswa kelas XI program keahlian Pengembangan Perangkat Lunak dan Gim (RPL) SMK Telkom Purwokerto:

| No | Nama Anggota | Anggota / Role | Pembagian Tugas & Tanggung Jawab |
|:---|:---|:---|:---|
| 1 | **Albeynaro Javier Wirajenedra** | Android Developer | Mendesain UI/UX Screen Utama, mengimplementasikan LazyVerticalGrid untuk Item, dan mengelola state Jetpack Compose. |
| 2 | **Fiky Alrasya** | Fullstack Developer | Membangun REST API Laravel, setup database MySQL (ULID PK), dan logic Firebase Messaging Service di Android. |
| 3 | **Kinara Aurelya Syahna** | System Analyst / QA | Menyusun Analisis Kebutuhan Data, merancang ERD, membuat Dokumentasi Proposal, dan melakukan testing skenario API. |
| 4 | **Nadya Almira Cordelia F.** | Android Developer | Mengimplementasikan Jetpack Compose untuk Form input (DatePicker/TimePicker), integrasi Retrofit API Client, dan menyusun DTO Request. |
| 5 | **Rangga Prayoga Wibowo** | AI & Voice Engineer | Merancang fitur AI Voice Announcement Modal, integrasi Speech-to-Text Parser, dan melakukan testing hardware mikrofon pada device. |

---

## 📝 Deskripsi Aplikasi
**LoFo (Lost & Found)** memisahkan data sensitif autentikasi dengan identitas sosial pengguna untuk menjamin privasi. Aplikasi ini bekerja secara kolaboratif di mana pengguna dapat mendaftarkan barang milik pribadi untuk mendapatkan **QR Code unik**. 

Ketika barang berstiker QR Code tersebut hilang dan ditemukan oleh orang lain, penemu cukup melakukan *scanning*, dan sistem otomatis memperbarui titik **Latitude & Longitude** terakhir barang tersebut ke database pemilik. Pemilik juga bisa mempublikasikan pengumuman kehilangan yang terintegrasi dengan data visual barang bawaan dari server AI.

### ⚡ Fitur Utama Sistem:
* **Autentikasi Aman & Modern**: Menggunakan Laravel Sanctum dengan Primary Key berbasis **ULID** (bukan auto-increment) demi keamanan global ID.
* **Manajemen Barang (Items)**: CRUD data barang pribadi lengkap dengan generator otomatis stiker QR Code (format SVG/Vector untuk performa lokal).
* **Pelacakan Lokasi (Scan-to-Update)**: Histori koordinat GPS barang ter-update otomatis begitu QR Code terpindai oleh komunitas sekolah.
* **Sistem Pengumuman Cerdas (Announcement)**: Membuat laporan kehilangan yang otomatis menarik aset gambar barang asli, mendukung input berbasis **AI Voice Speech-to-Text**, serta didukung **Scheduler Harian (Cron Job)** untuk mengirim push notification pengingat setiap jam 08:00 pagi via Google FCM API v1.

---

## 🛠️ Tech Stack & Arsitektur

### Mobile Client:
* **Language**: Kotlin 1.9+
* **UI Framework**: Jetpack Compose (Material 3 Component)
* **Asynchronous**: Kotlin Coroutines & Flow
* **Networking**: Retrofit 2 & OkHttp3
* **Image Loader**: Coil (dengan SvgDecoder Factory)
* **Push Notification**: Firebase Cloud Messaging (FCM) Service

### Backend API & Infrastructure:
* **Framework**: Laravel 11 (PHP 8.2+)
* **Database**: MySQL 8.0 (Relational Database)
* **Task Scheduling**: Laravel Schedule (di-trigger Cron Job VPS tiap menit)
* **Local Server**: Laragon Wamp / PHP Artisan Server
* **Production Server**: VPS Linux (Ubuntu Server) dengan Caddy Reverse Proxy

---

## 📦 Link Repository & Hasil Build APK

Silakan akses *source code* murni dan hasil kompilasi aplikasi siap install melalui tautan resmi di bawah ini:

* **Link Repository Backend (API Laravel)**:  
  `https://github.com/KyyTzy09/LoFo-API-Laravel` *(https://github.com/KyyTzy09/LoFo-API-Laravel)*

* **Link Repository Android App (Kotlin)**:  
  `https://github.com/KyyTzy09/Lofo-JP` *(https://github.com/KyyTzy09/Lofo-JP)*

* **Link Download APK Aplikasi (Siap Pakai)**:  
  [📥 Download LoFo_App_v1.0.apk](https://lofo-api.my.id/download/lofo-app.apk)  
  
  [📥 Download LoFo_App_v1.0.apk (Mediafire)](https://www.mediafire.com/file/gx4csk4deraehsp/LoFo.apk/file)

> ⚠️ **Catatan Install**: Karena aplikasi dibangun untuk keperluan proyek sekolah (PAS) dan belum dipublikasikan ke Google Play Store, saat instalasi akan muncul peringatan *Play Protect*. Silakan klik **"Detail / Selengkapnya"** lalu pilih **"Tetap Instal (Install Anyway)"**. Jangan lupa matikan mode *Jangan Ganggu (Do Not Disturb)* pada HP Anda agar pop-up banner notifikasi pengingat jam 8 pagi dapat muncul secara interaktif.