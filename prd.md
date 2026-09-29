# PRD — Sistem Manajemen Pendaftaran Staff Campus Fair 2027

**Versi:** 2.0 (Revisi: Input Manual, Stack Dasar, Deploy VPS + aaPanel + MariaDB)
**Status:** Draft untuk development
**Dibaca oleh:** Developer (termasuk junior) & AI coding assistant

---

## 1. Ringkasan Project

Sistem web internal untuk mengelola proses seleksi calon Staff Campus Fair 2027, mulai dari data pendaftar, proses wawancara oleh 4 pewawancara, sampai penempatan staff yang diterima ke dalam 7 divisi.

Data pendaftar hasil Google Form **tidak disinkronkan otomatis**. Admin akan **menyalin data dari spreadsheet dan menginputnya secara manual** ke dalam sistem melalui form input yang disediakan.

### 1.1 Tujuan
- Mengganti pengelolaan data manual di spreadsheet dengan database terstruktur.
- Mempermudah 4 pewawancara mencatat hasil wawancara & status wawancara.
- Mempermudah admin memutuskan diterima/tidak dan menempatkan ke divisi.
- Data antar fitur saling terhubung (1 sumber data, bukan data terpisah-pisah).

### 1.2 Role Pengguna
| Role | Jumlah Akun | Akses |
|---|---|---|
| **Admin** | 1 (penyelenggara utama) | Full access: input data pendaftar baru, lihat semua pendaftar, assign pewawancara, ubah status diterima/tidak, pilih divisi |
| **Pewawancara** | 4 akun terpisah | Hanya bisa login & melihat/mengelola pendaftar yang **di-assign ke dirinya** di fitur Wawancara. Tidak bisa akses fitur Staff CF, tidak bisa input pendaftar baru, tidak bisa ubah status diterima/tidak |

> Login menggunakan email + password sederhana. Tidak ada fitur register mandiri — akun dibuat manual oleh admin (5 akun total: 1 admin + 4 pewawancara), langsung lewat database (phpMyAdmin) atau seed script sederhana.

---

## 2. Alur Data (Big Picture)

```
Spreadsheet hasil Google Form (dipegang admin)
        ↓  [Admin salin & input manual satu per satu]
Form Input "Tambah Pendaftar" di sistem (Fitur 1)
        ↓
Database MariaDB (tabel "pendaftar")
        ↓
┌───────────────┬───────────────────┬──────────────────┐
│  Fitur 1       │  Fitur 2          │  Fitur 3         │
│  Pendaftar     │  Wawancara        │  Staff CF        │
│  (lihat semua, │  (admin assign ke │  (otomatis terisi│
│  input baru,   │  4 pewawancara,   │  saat status =   │
│  set status)   │  isi komentar)    │  "Diterima" +    │
│                │                   │  divisi dipilih) │
└───────────────┴───────────────────┴──────────────────┘
```

**Prinsip penting:** hanya ada **1 tabel utama pendaftar**. Fitur 1, 2, 3 hanyalah **tampilan berbeda** dari data yang sama + tabel pendukung (assignment wawancara, keputusan seleksi). Kalau status diubah di satu tempat, otomatis berubah di tempat lain — bukan data duplikat.

---

## 3. Struktur Database (Data Model)

> Database: **MariaDB**. Struktur tabel di bawah ini level konsep (nama tabel & kolom), silakan disesuaikan tipe data persisnya saat implementasi (mis. `VARCHAR`, `TEXT`, `ENUM`, `INT`, `DATETIME`).

### 3.1 Tabel `users` (Admin & Pewawancara)
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT, auto-increment | Primary key |
| nama | VARCHAR | Nama akun |
| email | VARCHAR (unique) | Untuk login |
| password_hash | VARCHAR | Password ter-enkripsi (pakai `password_hash()` bawaan PHP) |
| role | ENUM: `admin`, `pewawancara` | Menentukan akses |
| created_at | DATETIME | |

### 3.2 Tabel `pendaftar` (diinput manual oleh admin)
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT, auto-increment | Primary key |
| nama_lengkap | VARCHAR | |
| tahun_lulus | VARCHAR/INT | |
| asal_kampus_jurusan | TEXT | |
| no_whatsapp | VARCHAR | |
| alasan_mendaftar | TEXT | |
| pilihan_1 | ENUM divisi (lihat 3.5) | |
| alasan_pilihan_1 | TEXT | |
| pengalaman_pilihan_1 | TEXT | |
| pilihan_2 | ENUM divisi | |
| alasan_pilihan_2 | TEXT | |
| pengalaman_pilihan_2 | TEXT | |
| kesibukan_okt_jan | TEXT | |
| skor_komitmen | INT (1–10) | |
| skor_prioritas | INT (1–10) | |
| skor_keyakinan | INT (1–10) | |
| cv_link | VARCHAR (url) | Link file (Google Drive/lainnya), bukan file fisik yang di-upload ke server |
| portofolio_link | VARCHAR (url, nullable) | Boleh kosong |
| input_by | FK → users.id | Admin yang input data ini |
| created_at | DATETIME | Waktu data diinput ke sistem |

### 3.3 Tabel `wawancara` (relasi 1-ke-1 dengan pendaftar)
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT, auto-increment | Primary key |
| pendaftar_id | FK → pendaftar.id | |
| pewawancara_id | FK → users.id (nullable) | Diisi admin saat assign, kosong = belum di-assign |
| status_wawancara | ENUM: `belum`, `selesai` | Default `belum`. Diubah pewawancara lewat tombol keterangan |
| komentar | TEXT (nullable) | 1 kolom komentar, ditulis/diupdate oleh pewawancara yang di-assign |
| komentar_updated_at | DATETIME (nullable) | |

> Setiap kali admin input pendaftar baru di Fitur 1, sistem otomatis membuat 1 baris terkait di tabel `wawancara` (pewawancara_id masih kosong sampai di-assign).

### 3.4 Tabel `seleksi` (keputusan akhir, relasi 1-ke-1 dengan pendaftar)
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT, auto-increment | Primary key |
| pendaftar_id | FK → pendaftar.id (unique) | |
| status_diterima | ENUM: `pending`, `diterima`, `tidak_diterima` | Default `pending` |
| divisi_ditempatkan | ENUM divisi (nullable) | Wajib diisi kalau status = `diterima` |
| decided_by | FK → users.id | Admin yang memutuskan |
| decided_at | DATETIME (nullable) | |

> Sama seperti tabel `wawancara`, baris di tabel ini otomatis dibuat (status default `pending`) saat admin input pendaftar baru.

### 3.5 Daftar nilai `divisi` (dipakai di pilihan_1, pilihan_2, dan divisi_ditempatkan)
1. Humas
2. Acara
3. Konsumsi
4. Sponsorship
5. Perlengkapan
6. Keamanan
7. Publikasi, Dekorasi, dan Dokumentasi

---

## 4. Input Data Pendaftar (Manual)

Karena tidak ada sinkron otomatis, admin butuh cara cepat memasukkan data dari spreadsheet ke sistem.

**Fitur yang dibutuhkan (bagian dari Fitur 1):**
- Tombol **"Tambah Pendaftar"** yang membuka form input berisi semua kolom sesuai tabel `pendaftar` di atas (nama, tahun lulus, kampus & jurusan, no WA, alasan mendaftar, pilihan 1 & 2 beserta alasan/pengalaman, kesibukan, 3 skor skala 1-10, link CV, link portofolio).
- Setelah disimpan, otomatis membuat baris terkait di tabel `wawancara` dan `seleksi` seperti dijelaskan di atas.
- (Opsional, boleh menyusul di versi berikutnya) Fitur edit data pendaftar kalau ada salah input.

> Karena proses ini manual dan satu-satu, pastikan form input sederhana, jelas urutannya (ikuti urutan pertanyaan di Google Form asli), dan ada validasi dasar (field wajib tidak boleh kosong, skor harus 1-10).

---

## 5. Fitur 1 — Pendaftar

**Tujuan:** Tampilan utama berisi semua data pendaftar, jadi pusat kontrol status, sekaligus tempat admin menginput data baru (lihat bagian 4).

### Tampilan
- Tabel/list semua pendaftar, kolom minimal: Nama, Tahun Lulus, Pilihan 1, Pilihan 2, **Status Wawancara**, **Status Diterima**.
- Klik 1 baris → buka detail lengkap semua jawaban pendaftar tersebut.

### Komponen interaktif per pendaftar
1. **Badge/Label Status Wawancara** — otomatis menampilkan `Belum Wawancara` / `Sudah Wawancara`, diambil dari tabel `wawancara.status_wawancara`. Klik badge ini bisa diarahkan ke halaman detail di Fitur 2 (Wawancara).
2. **Tombol Diterima / Tidak Diterima** — mengubah `seleksi.status_diterima`.
3. **Dropdown Divisi** — muncul/aktif setelah status = `Diterima`. Memilih salah satu dari 7 divisi → tersimpan di `seleksi.divisi_ditempatkan` → otomatis muncul di Fitur 3 (Staff CF).

### Fitur tambahan yang disarankan
- Filter/search berdasarkan nama, status wawancara, status diterima, pilihan divisi.
- (Opsional, boleh menyusul) Tombol export ke Excel/CSV untuk laporan.

---

## 6. Fitur 2 — Wawancara

**Tujuan:** Tempat kerja 4 pewawancara mencatat hasil wawancara.

### Untuk Admin
- Bisa melihat semua pendaftar & meng-**assign manual** setiap pendaftar ke salah satu dari 4 pewawancara (dropdown pilih pewawancara per pendaftar).
- Bisa melihat progres wawancara ke-4 kelompok tersebut (berapa yang sudah/belum selesai per pewawancara).

### Untuk Pewawancara (setelah login)
- Hanya melihat **daftar pendaftar yang di-assign ke dirinya**.
- Klik salah satu pendaftar → tampil semua data pendaftar tersebut (read-only: nama, kampus, WA, alasan daftar, pilihan 1 & 2 beserta alasan/pengalaman, kesibukan, skor komitmen/prioritas/keyakinan, link CV & portofolio).
- Di bagian bawah halaman detail:
  - **Kolom input komentar** (textarea) + **tombol "Simpan Komentar"** → menyimpan ke `wawancara.komentar`.
  - **Tombol keterangan status** (toggle: `Tandai Selesai Wawancara` / `Tandai Belum Wawancara`) → mengubah `wawancara.status_wawancara`. Perubahan ini langsung terlihat di Fitur 1.

> Komentar bersifat **1 kolom per pendaftar** — jika pewawancara menyimpan ulang, komentar sebelumnya **tertimpa** (overwrite), bukan riwayat berlapis.

---

## 7. Fitur 3 — Staff CF

**Tujuan:** Menampilkan daftar final staff yang diterima, dikelompokkan per divisi.

### Tampilan
- 7 section/tab, satu per divisi:
  1. Divisi Humas
  2. Divisi Acara
  3. Divisi Konsumsi
  4. Divisi Sponsorship
  5. Divisi Perlengkapan
  6. Divisi Keamanan
  7. Divisi Publikasi, Dekorasi, dan Dokumentasi
- Di setiap divisi, tampilkan list nama-nama yang `seleksi.status_diterima = diterima` **dan** `seleksi.divisi_ditempatkan` = divisi tersebut.
- Data ini **otomatis terisi sendiri** — tidak ada input manual di halaman ini, murni hasil dari aksi di Fitur 1.
- Klik nama → bisa lihat detail data pendaftar (read-only) + hasil komentar wawancara sebagai referensi.

---

## 8. Tech Stack (Dasar / Sederhana)

Dipilih agar mudah dipahami junior developer, mudah di-deploy ke VPS pribadi via aaPanel, dan tidak butuh banyak dependency.

| Layer | Pilihan | Alasan |
|---|---|---|
| Bahasa Server-side | **PHP native** (tanpa framework berat, boleh pakai library ringan bila perlu seperti PDO untuk koneksi database) | aaPanel sudah mendukung PHP secara bawaan (one-click install), paling umum & paling banyak referensi/tutorial, mudah dipahami junior dev |
| Database | **MariaDB** | Sesuai permintaan, juga sudah tersedia one-click install di aaPanel + ada phpMyAdmin untuk kelola data lewat GUI |
| Koneksi ke Database | **PDO (PHP Data Objects)** | Cara standar & aman (mendukung prepared statement, mencegah SQL Injection) |
| Frontend | **HTML + CSS + sedikit JavaScript**, boleh dibantu **Bootstrap** untuk mempercepat styling dasar (tabel, form, tombol, badge) | Tidak perlu build tools/compiler, cukup dibuka langsung di browser, cocok untuk stack dasar |
| Autentikasi Login | Sesi PHP native (`$_SESSION`) + password di-hash pakai `password_hash()` / `password_verify()` | Bawaan PHP, tidak perlu library tambahan |
| Web Server | **Nginx atau Apache** (via aaPanel, tinggal pilih saat setup situs) | Bawaan pilihan di aaPanel |

> Stack ini sengaja dibuat sederhana (server-rendered, tanpa API terpisah/tanpa framework JS) supaya AI model yang lebih murah maupun developer junior bisa langsung membangunnya tanpa banyak konsep tambahan.

### 8.1 Struktur Folder (Referensi)

Struktur berikut adalah acuan level tinggi, boleh disesuaikan asal konsepnya tetap: tiap fitur punya folder sendiri, konfigurasi & kode yang dipakai berulang dipisah dari halaman fitur.

```
project-cf2027/
│
├── config/
│   └── database.php          # koneksi PDO ke MariaDB (host, db name, user, password)
│
├── includes/
│   ├── auth.php               # cek session login, helper redirect kalau belum login
│   ├── header.php             # bagian atas HTML yang dipakai berulang (navbar, dsb)
│   └── footer.php             # bagian bawah HTML yang dipakai berulang
│
├── auth/
│   ├── login.php
│   └── logout.php
│
├── pendaftar/                 # Fitur 1
│   ├── index.php              # list semua pendaftar
│   ├── tambah.php             # form input pendaftar baru
│   ├── detail.php             # detail 1 pendaftar (read-only)
│   └── update_status.php      # proses ubah status diterima/tidak & pilih divisi
│
├── wawancara/                 # Fitur 2
│   ├── index.php              # (admin) lihat 4 kelompok & assign pewawancara
│   ├── assign.php             # proses simpan assignment pewawancara
│   ├── detail.php             # (pewawancara) lihat detail + form komentar
│   └── simpan_komentar.php    # proses simpan komentar & status wawancara
│
├── staff/                     # Fitur 3
│   └── index.php              # tampilan 7 divisi berisi staff diterima
│
├── assets/
│   ├── css/
│   │   └── style.css          # styling custom di atas Bootstrap
│   ├── js/
│   │   └── script.js
│   └── img/
│
├── database/
│   └── schema.sql              # file SQL berisi CREATE TABLE untuk import di phpMyAdmin
│
└── index.php                   # halaman awal (redirect ke login atau dashboard)
```

**Catatan:**
- Tiap fitur (`pendaftar/`, `wawancara/`, `staff/`) punya folder sendiri berisi beberapa file PHP — mudah dicari & dipahami, dan tidak butuh routing khusus karena tiap file otomatis bisa diakses lewat URL-nya (misal `pendaftar/tambah.php` diakses di `namadomain.com/pendaftar/tambah.php`).
- `config/` & `includes/` dipisah supaya kode yang dipakai berulang (koneksi database, cek login, header/footer) tidak ditulis ulang di banyak file.
- `database/schema.sql` memudahkan langkah deployment (import struktur tabel lewat phpMyAdmin) — tinggal upload 1 file.

---

## 9. Deployment — VPS Pribadi via aaPanel

Langkah level tinggi (high-level), bukan panduan command-by-command:

1. **Install aaPanel** di VPS (jika belum ada).
2. Lewat dashboard aaPanel, **install stack PHP + MariaDB + web server** (Nginx/Apache) menggunakan fitur one-click install App Store bawaan aaPanel.
3. **Buat website/site baru** di aaPanel, arahkan domain/subdomain (atau pakai IP VPS langsung jika belum ada domain) ke folder project.
4. **Buat database MariaDB baru** beserta user & password-nya lewat menu Database di aaPanel (atau phpMyAdmin).
5. **Import struktur tabel** (bagian 3) ke database tersebut lewat phpMyAdmin — bisa disiapkan sebagai 1 file `.sql` berisi perintah `CREATE TABLE`.
6. **Upload seluruh source code** project (folder PHP & assets) ke folder site yang sudah dibuat, lewat File Manager aaPanel atau FTP/SFTP.
7. Sesuaikan **file konfigurasi koneksi database** (host, nama database, user, password) di kode agar sesuai dengan database yang sudah dibuat di aaPanel.
8. Buat 5 akun awal (1 admin + 4 pewawancara) langsung lewat phpMyAdmin atau lewat script seeding sederhana.
9. Test akses situs lewat browser menggunakan domain/IP VPS.
10. (Opsional) Pasang SSL gratis (Let's Encrypt) lewat menu SSL di aaPanel supaya situs bisa diakses via HTTPS.

---

## 10. Tema Desain (UI)

Berdasarkan referensi desain yang dilampirkan (gaya landing page ReelUp), dengan **latar dasar putih**.

### Warna
| Nama | Hex (perkiraan) | Pemakaian |
|---|---|---|
| Background utama | `#FFFFFF` | Latar semua halaman |
| Teks utama | `#111111` | Judul, teks penting |
| Teks sekunder | `#6B7280` | Deskripsi, label kecil |
| Tombol utama / aksen gelap | `#111111` (bg) + teks putih | Tombol "Simpan", "Assign", "Tambah Pendaftar", dsb |
| Aksen kuning | `#F5C242` | Card statistik / status highlight (misal badge "Sudah Wawancara") |
| Aksen ungu muda | `#E7DAF7` | Background section/card alternatif, badge netral |
| Aksen oranye/coral | `#F2653C` | Badge status penting (misal "Tidak Diterima" atau notifikasi) |
| Border/divider | `#E5E7EB` | Garis pemisah tabel/card |

### Gaya Umum
- Rounded corner besar untuk card & tombol (mengikuti referensi, ± 16–24px radius).
- Tombol utama: bentuk pill/rounded penuh, warna hitam solid dengan teks putih (seperti tombol "Get Started" pada referensi).
- Badge status pakai warna solid pastel (kuning/ungu/oranye) dengan teks gelap, bentuk pill kecil.
- Layout bersih, banyak white space, tipografi sans-serif tegas untuk judul.

---

## 11. Ringkasan Hubungan Antar Fitur (untuk memastikan tidak ada yang salah paham)

| Aksi | Dilakukan di | Efeknya terlihat di |
|---|---|---|
| Admin input data pendaftar baru | Fitur 1 | Otomatis muncul di Fitur 1 & baris kosong dibuat di Fitur 2 |
| Admin assign pendaftar ke pewawancara | Fitur 2 | Muncul di list pewawancara terkait (Fitur 2) |
| Pewawancara simpan komentar & tandai selesai wawancara | Fitur 2 | Status wawancara berubah di Fitur 1 |
| Admin klik "Diterima" + pilih divisi | Fitur 1 | Nama otomatis muncul di divisi terkait (Fitur 3) |
| Admin klik "Tidak Diterima" | Fitur 1 | Nama tidak muncul di Fitur 3 |

---

## 12. Di Luar Cakupan (Out of Scope) — versi 2.0
- Sinkronisasi otomatis dari Google Form (sudah diputuskan input manual saja).
- Notifikasi otomatis (email/WA) ke pendaftar.
- Riwayat/log perubahan status secara detail (siapa ubah apa kapan) — bisa jadi pengembangan lanjutan.
- Upload file CV/Portofolio ke server sendiri (versi ini cukup pakai link, misalnya Google Drive).
- Multi komentar/riwayat komentar per pendaftar (saat ini hanya 1 kolom komentar yang bisa ditimpa).
- Fitur edit data pendaftar setelah diinput (boleh ditambahkan di versi berikutnya bila dibutuhkan).