# SIM-PKM: Sistem Manajemen & Review Proposal PKM Mahasiswa Berbasis Web

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="320" alt="Laravel Logo">
</p>

Aplikasi web modern berbasis **Laravel 11**, **PHP 8.4**, **Bootstrap 5**, dan **MySQL** yang dirancang untuk mengelola seluruh siklus pengajuan, bimbingan, telaah (*review*), revisi bertingkat (*document versioning*), serta pemantauan (*monitoring*) proposal Program Kreativitas Mahasiswa (PKM) di lingkungan perguruan tinggi.

---

## 🌟 Fitur Utama Sistem

### 1. Modul Mahasiswa (Ketua Tim Pengusul)
* **Pengajuan Proposal Dinamis**:
  * Input judul proposal, skema PKM (PKM-RE, PKM-K, PKM-KC, PKM-PM, PKM-PI, PKM-VGK, PKM-GFT, dll.).
  * Unggah berkas naskah proposal format PDF (otomatis tercatat sebagai Versi 1).
  * Susunan anggota tim mahasiswa yang dapat ditambah/dikurangi secara dinamis dengan tugas (*jobdesk*) masing-masing.
* **Manajemen Revisi Naskah (*Document Versioning*)**:
  * Mahasiswa dapat mengunggah berkas revisi naskah baru (v2, v3, dst.) beserta rangkuman catatan perbaikan.
  * Status usulan otomatis kembali menjadi *Sedang Dibimbing* saat berkas revisi diunggah.
* **Checklist Evaluasi & Pelacak Progres**:
  * Mahasiswa dapat meninjau butir-butir catatan evaluasi dari dosen pembimbing.
  * Tombol checklist interaktif *"Tandai Sudah Diperbaiki"* dengan coret teks otomatis dan *progress bar* persentase penyelesaian (misal: 100% selesai).

---

### 2. Modul Dosen Pembimbing
* **Klaim Usulan Proposal (*Race-Condition Safe*)**:
  * Dosen dapat melihat daftar proposal yang baru diajukan dan mengklaim usulan bimbingan.
  * Menggunakan mekanisme database transaction & locking (`lockForUpdate`) untuk mencegah dua dosen mengklaim usulan yang sama secara bersamaan.
* **Workspace Review Interaktif (Split-View Layout)**:
  * **Sisi Kiri**: PDF Viewer terintegrasi dengan pemilih versi naskah (*version switcher*) untuk membandingkan perbaikan naskah antar versi tanpa memuat ulang halaman.
  * **Sisi Kanan**: Form dan daftar catatan evaluasi review per nomor halaman atau catatan umum.
* **Manajemen Review Real-Time (AJAX CRUD)**:
  * Tambah, edit, dan hapus catatan review secara asinkron tanpa mengganggu posisi membaca dokumen PDF.
* **Kontrol Status Proposal**:
  * Dosen dapat memperbarui status usulan langsung dari header workspace review:
    * 📖 **Sedang Dibimbing** (`sedang_dibimbing`)
    * ⚠️ **Perlu Revisi Mahasiswa** (`revisi`)
    * ✅ **Selesai / Disetujui** (`selesai`)

---

### 3. Modul Administrator Kampus
* **Dashboard Monitoring & Metrik KPI Real-Time**:
  * Widget statistik: Total Usulan, Mencari Pembimbing, Sedang Dibimbing, Perlu Revisi, dan Usulan Selesai/Final.
* **Pencarian & Penyaringan Lanjutan**:
  * Filter berdasarkan status usulan dan skema PKM.
  * Pencarian kata kunci fleksibel (judul usulan, NIM/Nama mahasiswa, NIP/Nama dosen).
* **Modal Audit Detail Usulan**:
  * Rincian susunan tim pelaksana beserta jobdesk.
  * Riwayat seluruh arsip versi dokumen PDF yang pernah diunggah.
  * Rekapitulasi seluruh riwayat review dosen pembimbing dan status penyelesaiannya.
* **Menu Pengaturan (Setting)**:
  * **Add Mahasiswa & Data Mahasiswa**: Mendaftarkan mahasiswa yang berhak memiliki akses masuk (*login*) ke sistem, edit data, dan reset password.
  * **Add Dosen & Data Dosen Pembimbing**: Mendaftarkan dosen pembimbing lengkap dengan bidang kepakaran/keahlian serta **Penetapan Jenis Skema PKM Bimbingan** (opsi `All` untuk melihat semua usulan, atau skema spesifik seperti `PKM-K`, `PKM-KC`, dll. untuk pembatasan telaah & klaim usulan).
  * **Proteksi Integritas Data**: Mahasiswa dan dosen yang memiliki keterkaitan usulan proposal aktif diproteksi dari penghapusan secara otomatis.

---

### 4. Fitur Demo Quick-Login 1-Klik
Tersedia tombol demo instan di halaman login (`/login`) untuk mempermudah evaluasi:

| Peran | Akun Demo | Email | Password | Keterangan |
|---|---|---|---|---|
| **Administrator** | Administrator PKM | `admin@example.com` | `password` | Akses Dashboard Monitoring & Setting Pengguna |
| **Mahasiswa** | Budi Santoso | `mahasiswa@example.com` | `password` | Ketua Tim Pengusul PKM |
| **Dosen 1** | Dr. Ir. Hendra Wijaya, M.Kom. | `dosen1@example.com` | `password` | Kepakaran: AI & Internet of Things |
| **Dosen 2** | Prof. Dr. Siti Rahmawati, M.T. | `dosen2@example.com` | `password` | Kepakaran: Kewirausahaan & Bisnis Digital |

---

## 🛠️ Arsitektur & Teknologi

* **Framework Backend**: Laravel 11 (PHP 8.4+)
* **Database**: MySQL 8.4 (InnoDb, Foreign Key Cascades & Strict Integrity)
* **Frontend UI**: Blade Templating + Bootstrap 5.3 + Bootstrap Icons
* **PDF Handling**: Native Embedded HTML5 Object/Iframe PDF Viewer
* **AJAX**: Fetch API & XMLHttpRequest dengan proteksi CSRF token
* **Pengujian Otomatis**: PHPUnit / Laravel Feature Test Suite (41 Tests, 153 Assertions, 100% Passed)

---

## 📁 Struktur Direktori Utama

```text
proposalUkm/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AdminMahasiswaController.php  # Manajemen Akun Mahasiswa oleh Admin
│   │   │   ├── AdminDosenController.php      # Manajemen Akun Dosen Pembimbing oleh Admin
│   │   │   ├── AdminProposalController.php   # Monitoring Usulan & Metrik KPI
│   │   │   ├── AuthController.php            # Login, Logout & Demo Quick-Login
│   │   │   ├── DosenProposalController.php   # Claim, Review Workspace, AJAX CRUD
│   │   │   └── MahasiswaProposalController.php # Submit Proposal, Revisi, Resolution
│   │   └── Middleware/
│   │       └── CheckRole.php                 # Role-based Access Control (mahasiswa/dosen/admin)
│   └── Models/
│       ├── Proposal.php                      # Model Usulan PKM
│       ├── ProposalDocument.php              # Model Arsip Versi Naskah PDF
│       ├── ProposalMember.php                # Model Anggota Tim Mahasiswa & Jobdesk
│       ├── ProposalReview.php                # Model Catatan Evaluasi Review Dosen
│       └── User.php                          # Model Akun Pengguna (Mahasiswa, Dosen, Admin)
├── database/
│   ├── migrations/                           # Skema Tabel MySQL
│   └── seeders/DatabaseSeeder.php            # Data Awal Pengguna Demo & Proposal
├── resources/
│   └── views/
│       ├── admin/                            # Tampilan Dashboard Monitoring, Mahasiswa & Dosen
│       ├── auth/                             # Tampilan Form Login & Quick-Login
│       ├── dosen/                            # Tampilan Claim Usulan & Workspace Review Split-View
│       ├── mahasiswa/                        # Tampilan Dashboard Usulan & Form Pengajuan
│       └── layouts/app.blade.php             # Master Layout & Navbar Navigasi Terpadu
├── routes/web.php                            # Definisi Seluruh Rute Sistem
└── tests/Feature/                            # Automated Feature Test Suite
```

---

## 🚀 Panduan Instalasi Lokal

### 1. Kloning Repositori
```bash
git clone https://github.com/dedirus/proposalPkm.git
cd proposalPkm
```

### 2. Instal Dependensi Composer
```bash
composer install
```

### 3. Konfigurasi Lingkungan (`.env`)
Salin berkas konfigurasi lingkungan dan sesuaikan kredensial database Anda:
```bash
cp .env.example .env
php artisan key:generate
```

Sesuaikan parameter koneksi database di berkas `.env`:
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=proposal_ukm
DB_USERNAME=root
DB_PASSWORD=root
```

### 4. Tautkan Storage Simbolik
```bash
php artisan storage:link
```

### 5. Jalankan Migrasi & Seeder Database
```bash
php artisan migrate:fresh --seed
```

### 6. Jalankan Server Aplikasi
```bash
php artisan serve
```
Akses aplikasi melalui peramban web di: `http://127.0.0.1:8000` atau konfigurasi virtual host ServBay Anda.

---

## 🧪 Menjalankan Pengujian Otomatis (*Automated Tests*)

Untuk memverifikasi seluruh fungsionalitas alur kerja proposal, hak akses, validasi, dan manipulasi data:

```bash
php artisan test
```

Hasil pengujian:
```text
PASS  Tests\Feature\AdminDosenManagementTest (13 tests, 54 assertions)
PASS  Tests\Feature\AdminMahasiswaManagementTest (9 tests, 34 assertions)
PASS  Tests\Feature\ProposalPkmWorkflowTest (18 tests, 64 assertions)
PASS  Tests\Feature\ExampleTest (1 test, 1 assertion)

Tests:    41 passed (153 assertions)
Duration: ~3.0s
```

---

## 📄 Lisensi
Sistem ini dirilis di bawah lisensi open-source [MIT License](LICENSE).
