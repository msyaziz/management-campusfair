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

| Email | Role |
|---|---|
| `admin@campusfair.id` | Admin |
| `interviewer1@campusfair.id` | Pewawancara |
| `interviewer2@campusfair.id` | Pewawancara |
| `interviewer3@campusfair.id` | Pewawancara |
| `interviewer4@campusfair.id` | Pewawancara |

> ⚠️ **Penting:** Ganti password default sebelum digunakan di server produksi!

---

## ⚙️ Tech Stack

- **Backend:** PHP Native (tanpa framework)
- **Database:** MariaDB / MySQL
- **Frontend:** Bootstrap 5 + Custom CSS
- **Font:** Plus Jakarta Sans (Google Fonts, via CDN)

---

## 🚀 Panduan Instalasi (Lokal)

### Prasyarat
- PHP 8.0 atau lebih baru
- MySQL / MariaDB
- Ekstensi PHP: `pdo`, `pdo_mysql`

### Langkah-langkah

**1. Clone repositori**
```bash
git clone https://github.com/USERNAME/management-campusfair.git
cd management-campusfair
```

**2. Buat database dan impor schema**

Buka terminal MySQL atau PHPMyAdmin, lalu jalankan:
```bash
mysql -u root -p < database/schema.sql
```
Perintah di atas akan membuat database `campusfair2027`, membuat semua tabel, dan menyisipkan 5 akun bawaan (seed data).

**3. Sesuaikan konfigurasi database**

Edit file `config/database.php` dan sesuaikan kredensial koneksi:
```php
$host     = 'localhost';
$dbname   = 'campusfair2027';
$username = 'root';          // Sesuaikan
$password = '';               // Sesuaikan
```

**4. Jalankan server lokal**

Gunakan PHP Built-in Server:
```bash
php -S localhost:8000
```
Atau jalankan skrip helper yang sudah disediakan:
```bash
# Linux / Mac
chmod +x serve.sh
./serve.sh

# Windows
serve.bat
```

**5. Buka di browser**

Akses [http://localhost:8000](http://localhost:8000). Anda akan diarahkan ke halaman Login.

---

## 🌐 Panduan Deploy ke VPS (aaPanel)

1. **Buat website** di aaPanel (misal: `campusfair.domain.com`).
2. **Upload** semua file proyek ke document root (`/www/wwwroot/campusfair.domain.com/`).
3. **Buat database** MySQL/MariaDB melalui aaPanel → Database → Add database.
4. **Impor schema:** Buka phpMyAdmin, pilih database yang baru dibuat, lalu impor file `database/schema.sql`.
5. **Edit** `config/database.php` sesuai kredensial database yang dibuat di aaPanel.
6. Pastikan PHP versi 8.0+ aktif di website settings.

---

## 📂 Struktur Folder

```
management-campusfair/
├── assets/
│   └── css/
│       └── style.css          # Custom theme (Plus Jakarta Sans, pastel badges)
├── auth/
│   ├── login.php              # Halaman login
│   └── logout.php             # Proses logout
├── config/
│   └── database.php           # Konfigurasi koneksi PDO
├── database/
│   └── schema.sql             # DDL tabel + seed data akun
├── includes/
│   ├── auth.php               # Helper autentikasi & otorisasi
│   ├── header.php             # Template navbar & head HTML
│   └── footer.php             # Template footer & script
├── pendaftar/
│   ├── index.php              # Tabel semua pendaftar (Fitur 1)
│   ├── tambah.php             # Form input pendaftar baru
│   ├── proses_tambah.php      # Proses simpan data pendaftar
│   ├── detail.php             # Detail & update status pendaftar
│   └── update_status.php      # Proses update status diterima/tidak
├── wawancara/
│   ├── index.php              # Daftar wawancara (Fitur 2)
│   ├── assign.php             # Proses assign pewawancara
│   ├── detail.php             # Detail & form komentar wawancara
│   └── simpan_komentar.php    # Proses simpan komentar
├── staff/
│   └── index.php              # Daftar staff final (Fitur 3)
├── index.php                  # Router utama (redirect sesuai role)
├── serve.sh                   # Skrip jalankan server lokal (Linux/Mac)
├── serve.bat                  # Skrip jalankan server lokal (Windows)
├── prd.md                     # Product Requirements Document
└── README.md                  # Dokumentasi ini
```

---

## 📄 Lisensi

Proyek internal Campus Fair 2027. Hak cipta dilindungi.
