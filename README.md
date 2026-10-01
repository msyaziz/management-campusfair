# 🎓 Sistem Manajemen Pendaftaran Staff Campus Fair 2027

Sistem web internal untuk mengelola proses seleksi calon Staff Campus Fair 2027 — mulai dari input data pendaftar, proses wawancara oleh 4 pewawancara, hingga penempatan staff ke 7 divisi.

---

## 📋 Fitur Utama

| Fitur | Deskripsi |
|---|---|
| **Data Pendaftar** | Input manual, lihat semua data, update status pendaftar |
| **Wawancara** | Admin assign pewawancara, pewawancara isi komentar & tandai selesai |
| **Staff Final** | Otomatis menampilkan pendaftar yang sudah diterima & ditempatkan di divisi |

## 🔐 Akun Default

Semua akun menggunakan password default: `password123`

| Username | Role |
|---|---|
| `admin` | Admin |
| `pewawancara1` | Pewawancara |
| `pewawancara2` | Pewawancara |
| `pewawancara3` | Pewawancara |
| `pewawancara4` | Pewawancara |

> ⚠️ **Penting:** Segera ganti password default sebelum digunakan!

---

## 🚀 Panduan Deployment ke aaPanel (High-Level)

Tutorial ini khusus dibuat agar sangat mudah dipahami, langkah demi langkah, untuk mendeploy website ini ke server Linux yang menggunakan **aaPanel**.

### Tahap 1: Persiapan Server aaPanel
1. Login ke dashboard aaPanel Anda.
2. Buka menu **App Store**.
3. Pastikan Anda sudah menginstall:
   - Web Server (Nginx atau Apache)
   - Database (MySQL atau MariaDB)
   - **PHP 8.4** (Website ini menggunakan PHP versi terbaru).

### Tahap 2: Membuat Website di aaPanel
1. Masuk ke menu **Website** -> Klik tombol **Add site**.
2. Masukkan **Domain** Anda (contoh: `campusfair.com`).
3. Pada bagian **Database**, pilih `MySQL` (sistem aaPanel akan otomatis membuatkan *Database Name*, *Username*, dan *Password*). **Catat ketiga data ini baik-baik!**
4. Pada opsi **PHP Version**, pastikan Anda memilih **PHP-84**.
5. Klik **Submit**.

### Tahap 3: Upload Source Code
1. Klik area *Document Root* (nama folder) pada baris website yang baru saja dibuat. Anda akan dibawa ke **Files** manager aaPanel.
2. Hapus file *default* seperti `index.html` dan `.user.ini` (jika ada).
3. **Upload** seluruh file codingan dari repositori ini ke folder tersebut. 
   *(Pastikan file `index.php` berada di luar, tidak masuk ke dalam sub-folder).*

### Tahap 4: Import Database
1. Buka menu **Database** di kiri aaPanel.
2. Cari nama database Anda di daftar, lalu klik tombol **phpMyAdmin**.
3. Di dalam phpMyAdmin, klik nama database Anda di kolom sebelah kiri.
4. Klik tab **Import** (di bagian atas) -> Pilih file `database/schema.sql` dari folder codingan -> Klik tombol **Go** atau **Import** di bagian bawah.

### Tahap 5: Menyambungkan Web ke Database
1. Kembali ke menu **Files** manager di aaPanel.
2. Buka folder `config/` dan edit file `database.php` (klik kanan -> Edit).
3. Ubah bagian ini:
   ```php
   $dbname   = 'NAMA_DATABASE_DARI_AAPANEL';
   $username = 'USER_DATABASE_DARI_AAPANEL';
   $password = 'PASSWORD_DARI_AAPANEL';
   ```
   Ganti nilai-nilai tersebut dengan data yang Anda catat pada Tahap 2 tadi.
4. Klik ikon Save (simpan).

**🎉 Selesai!** Website Anda sekarang sudah live dan tersambung ke database aaPanel. Silakan buka domain Anda di browser.

---

## 📂 Struktur Folder Utama

- `assets/` : File CSS dan kustomisasi tampilan.
- `auth/` : Proses Login & Logout.
- `config/` : Pengaturan koneksi Database.
- `database/` : File `schema.sql` untuk diimpor ke database.
- `includes/` : Komponen pendukung (Header, Footer, Session Auth).
- `pendaftar/` : Modul Pendaftar (Fitur 1).
- `wawancara/` : Modul Wawancara (Fitur 2).
- `staff/` : Modul Staff Final (Fitur 3).
- `index.php` : File utama yang mengarahkan user setelah login.
