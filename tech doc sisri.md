# Dokumen Teknis Sistem Informasi Skripsi (SISRI)

## 1. PENDAHULUAN

### 1.1 Tujuan Dokumen
Dokumen teknis ini dibuat untuk memberikan panduan lengkap dalam pengembangan Sistem Informasi Skripsi (SISRI) menggunakan Laravel 12 dan TailwindCSS. Dokumen ini mencakup arsitektur sistem, spesifikasi teknis, struktur database, dan implementasi fitur-fitur utama.

### 1.2 Ruang Lingkup Sistem
SISRI adalah aplikasi berbasis web yang mengelola proses tugas akhir/skripsi mahasiswa, mulai dari pengajuan topik hingga sidang skripsi, dengan empat tipe pengguna: Admin UTM, Mahasiswa, Dosen, dan Koordinator Program Studi.

## 2. SPESIFIKASI TEKNIS

### 2.1 Technology Stack
- **Backend Framework**: Laravel 12
- **Frontend Styling**: TailwindCSS 3.x
- **Database**: MySQL 8.0+
- **PHP Version**: PHP 8.2+
- **Node.js**: v18+ (untuk build tools)
- **Package Manager**: Composer, NPM/Yarn

### 2.2 Dependensi Utama Laravel
```json
{
  "require": {
    "php": "^8.2",
    "laravel/framework": "^12.0",
    "laravel/sanctum": "^4.0",
    "laravel/breeze": "^2.0",
    "spatie/laravel-permission": "^6.0",
    "barryvdh/laravel-dompdf": "^3.0",
    "maatwebsite/excel": "^3.1"
  }
}
```

### 2.3 Dependensi Frontend
```json
{
  "devDependencies": {
    "@tailwindcss/forms": "^0.5.7",
    "autoprefixer": "^10.4.16",
    "postcss": "^8.4.32",
    "tailwindcss": "^3.4.0",
    "vite": "^5.0"
  }
}
```

## 3. ARSITEKTUR SISTEM

### 3.1 Arsitektur MVC
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── DashboardController.php
│   │   │   ├── MahasiswaController.php
│   │   │   ├── DosenController.php
│   │   │   ├── KoordinatorController.php
│   │   │   └── PeriodeController.php
│   │   ├── Mahasiswa/
│   │   │   ├── TopikController.php
│   │   │   ├── BimbinganProposalController.php
│   │   │   ├── SeminarProposalController.php
│   │   │   ├── BimbinganSkripsiController.php
│   │   │   └── SidangSkripsiController.php
│   │   ├── Dosen/
│   │   │   ├── ValidasiUsulanController.php
│   │   │   ├── BimbinganController.php
│   │   │   ├── ValidasiSeminarController.php
│   │   │   └── NilaiController.php
│   │   └── Koordinator/
│   │       ├── BidangMinatController.php
│   │       ├── PenjadwalanController.php
│   │       └── DaftarNilaiController.php
│   ├── Middleware/
│   │   ├── CheckRole.php
│   │   └── CheckPeriodeAktif.php
│   └── Requests/
│       ├── TopikRequest.php
│       ├── BimbinganRequest.php
│       └── PenjadwalanRequest.php
├── Models/
│   ├── User.php
│   ├── Mahasiswa.php
│   ├── Dosen.php
│   ├── Topik.php
│   ├── Bimbingan.php
│   ├── SeminarProposal.php
│   ├── SidangSkripsi.php
│   ├── BidangMinat.php
│   └── Nilai.php
└── Services/
    ├── BimbinganService.php
    ├── PenjadwalanService.php
    └── NotifikasiService.php
```

### 3.2 Struktur View (Blade Templates)
```
resources/
├── views/
│   ├── layouts/
│   │   ├── app.blade.php
│   │   ├── admin.blade.php
│   │   ├── mahasiswa.blade.php
│   │   ├── dosen.blade.php
│   │   └── koordinator.blade.php
│   ├── admin/
│   │   ├── dashboard.blade.php
│   │   ├── mahasiswa/
│   │   ├── dosen/
│   │   └── koordinator/
│   ├── mahasiswa/
│   │   ├── dashboard.blade.php
│   │   ├── ajukan-topik.blade.php
│   │   ├── bimbingan-proposal.blade.php
│   │   └── sidang.blade.php
│   ├── dosen/
│   │   ├── dashboard.blade.php
│   │   ├── validasi-usulan.blade.php
│   │   └── bimbingan.blade.php
│   └── koordinator/
│       ├── dashboard.blade.php
│       ├── bidang-minat.blade.php
│       └── penjadwalan.blade.php
└── css/
    └── app.css
```

## 4. DESAIN DATABASE

### 4.1 Tabel Users & Authentication
```sql
-- Tabel users (untuk semua role)
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'mahasiswa', 'dosen', 'koordinator') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel units (fakultas, jurusan, prodi)
CREATE TABLE units (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id BIGINT UNSIGNED NULL,
    type ENUM('fakultas', 'jurusan', 'prodi') NOT NULL,
    kode VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES units(id) ON DELETE CASCADE
);

-- Tabel mahasiswa
CREATE TABLE mahasiswa (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED UNIQUE NOT NULL,
    nim VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    prodi_id BIGINT UNSIGNED NOT NULL,
    angkatan VARCHAR(4) NOT NULL,
    email VARCHAR(100),
    no_hp VARCHAR(15),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (prodi_id) REFERENCES units(id) ON DELETE RESTRICT
);

-- Tabel dosen
CREATE TABLE dosen (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED UNIQUE NOT NULL,
    nip VARCHAR(20) UNIQUE,
    nidn VARCHAR(20) UNIQUE,
    nama VARCHAR(100) NOT NULL,
    prodi_id BIGINT UNSIGNED NOT NULL,
    email VARCHAR(100),
    no_hp VARCHAR(15),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (prodi_id) REFERENCES units(id) ON DELETE RESTRICT
);

-- Tabel koordinator_prodi
CREATE TABLE koordinator_prodi (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    dosen_id BIGINT UNSIGNED NOT NULL,
    prodi_id BIGINT UNSIGNED NOT NULL,
    tahun_mulai YEAR NOT NULL,
    tahun_selesai YEAR NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (dosen_id) REFERENCES dosen(id) ON DELETE CASCADE,
    FOREIGN KEY (prodi_id) REFERENCES units(id) ON DELETE CASCADE,
    UNIQUE KEY unique_active_koordinator (prodi_id, is_active)
);
```

### 4.2 Tabel Bidang Minat & Topik
```sql
-- Tabel bidang_minat
CREATE TABLE bidang_minat (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    prodi_id BIGINT UNSIGNED NOT NULL,
    nama VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (prodi_id) REFERENCES units(id) ON DELETE CASCADE
);

-- Tabel topik_skripsi
CREATE TABLE topik_skripsi (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mahasiswa_id BIGINT UNSIGNED NOT NULL,
    bidang_minat_id BIGINT UNSIGNED NOT NULL,
    judul TEXT NOT NULL,
    file_proposal VARCHAR(255),
    status ENUM('menunggu', 'diterima', 'ditolak') DEFAULT 'menunggu',
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE,
    FOREIGN KEY (bidang_minat_id) REFERENCES bidang_minat(id) ON DELETE RESTRICT
);

-- Tabel usulan_pembimbing
CREATE TABLE usulan_pembimbing (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    topik_id BIGINT UNSIGNED NOT NULL,
    dosen_id BIGINT UNSIGNED NOT NULL,
    urutan TINYINT NOT NULL CHECK (urutan IN (1, 2)),
    status ENUM('menunggu', 'diterima', 'ditolak') DEFAULT 'menunggu',
    jangka_waktu DATE NULL,
    catatan TEXT,
    tanggal_respon TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (topik_id) REFERENCES topik_skripsi(id) ON DELETE CASCADE,
    FOREIGN KEY (dosen_id) REFERENCES dosen(id) ON DELETE CASCADE,
    UNIQUE KEY unique_topik_urutan (topik_id, urutan)
);
```

### 4.3 Tabel Bimbingan
```sql
-- Tabel bimbingan
CREATE TABLE bimbingan (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    topik_id BIGINT UNSIGNED NOT NULL,
    dosen_id BIGINT UNSIGNED NOT NULL,
    jenis ENUM('proposal', 'skripsi') NOT NULL,
    pokok_bimbingan TEXT NOT NULL,
    file_bimbingan VARCHAR(255),
    pesan_mahasiswa TEXT,
    pesan_dosen TEXT,
    file_revisi VARCHAR(255),
    status ENUM('menunggu', 'direvisi', 'disetujui') DEFAULT 'menunggu',
    tanggal_bimbingan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tanggal_respon TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (topik_id) REFERENCES topik_skripsi(id) ON DELETE CASCADE,
    FOREIGN KEY (dosen_id) REFERENCES dosen(id) ON DELETE CASCADE
);
```

### 4.4 Tabel Periode & Jadwal Sidang
```sql
-- Tabel periode
CREATE TABLE periode (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(50) NOT NULL,
    jenis ENUM('ganjil', 'genap') NOT NULL,
    tahun_akademik VARCHAR(9) NOT NULL, -- contoh: 2023/2024
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    is_active BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel jadwal_sidang
CREATE TABLE jadwal_sidang (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    prodi_id BIGINT UNSIGNED NOT NULL,
    periode_id BIGINT UNSIGNED NOT NULL,
    jenis ENUM('seminar_proposal', 'sidang_skripsi') NOT NULL,
    nama_periode VARCHAR(100) NOT NULL,
    tanggal_buka DATETIME NOT NULL,
    tanggal_tutup DATETIME NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (prodi_id) REFERENCES units(id) ON DELETE CASCADE,
    FOREIGN KEY (periode_id) REFERENCES periode(id) ON DELETE CASCADE
);
```

### 4.5 Tabel Pendaftaran & Pelaksanaan Sidang
```sql
-- Tabel pendaftaran_sidang
CREATE TABLE pendaftaran_sidang (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    topik_id BIGINT UNSIGNED NOT NULL,
    jadwal_sidang_id BIGINT UNSIGNED NOT NULL,
    jenis ENUM('seminar_proposal', 'sidang_skripsi') NOT NULL,
    status_pembimbing_1 ENUM('menunggu', 'disetujui', 'ditolak') DEFAULT 'menunggu',
    status_pembimbing_2 ENUM('menunggu', 'disetujui', 'ditolak') DEFAULT 'menunggu',
    status_koordinator ENUM('menunggu', 'disetujui', 'ditolak') DEFAULT 'menunggu',
    catatan_pembimbing_1 TEXT,
    catatan_pembimbing_2 TEXT,
    catatan_koordinator TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (topik_id) REFERENCES topik_skripsi(id) ON DELETE CASCADE,
    FOREIGN KEY (jadwal_sidang_id) REFERENCES jadwal_sidang(id) ON DELETE CASCADE
);

-- Tabel pelaksanaan_sidang
CREATE TABLE pelaksanaan_sidang (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pendaftaran_sidang_id BIGINT UNSIGNED NOT NULL,
    tanggal_sidang DATETIME NOT NULL,
    tempat VARCHAR(100),
    status ENUM('dijadwalkan', 'selesai', 'dibatalkan') DEFAULT 'dijadwalkan',
    berita_acara VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pendaftaran_sidang_id) REFERENCES pendaftaran_sidang(id) ON DELETE CASCADE
);

-- Tabel penguji_sidang
CREATE TABLE penguji_sidang (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pelaksanaan_sidang_id BIGINT UNSIGNED NOT NULL,
    dosen_id BIGINT UNSIGNED NOT NULL,
    role ENUM('pembimbing_1', 'pembimbing_2', 'penguji_1', 'penguji_2', 'penguji_3') NOT NULL,
    ttd_berita_acara BOOLEAN DEFAULT FALSE,
    tanggal_ttd TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pelaksanaan_sidang_id) REFERENCES pelaksanaan_sidang(id) ON DELETE CASCADE,
    FOREIGN KEY (dosen_id) REFERENCES dosen(id) ON DELETE CASCADE
);
```

### 4.6 Tabel Revisi & Nilai
```sql
-- Tabel revisi_sidang
CREATE TABLE revisi_sidang (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pelaksanaan_sidang_id BIGINT UNSIGNED NOT NULL,
    dosen_id BIGINT UNSIGNED NOT NULL,
    file_revisi VARCHAR(255),
    catatan TEXT,
    status ENUM('menunggu', 'disetujui', 'revisi_ulang') DEFAULT 'menunggu',
    tanggal_submit TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tanggal_validasi TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pelaksanaan_sidang_id) REFERENCES pelaksanaan_sidang(id) ON DELETE CASCADE,
    FOREIGN KEY (dosen_id) REFERENCES dosen(id) ON DELETE CASCADE
);

-- Tabel nilai
CREATE TABLE nilai (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pelaksanaan_sidang_id BIGINT UNSIGNED NOT NULL,
    dosen_id BIGINT UNSIGNED NOT NULL,
    jenis_nilai ENUM('bimbingan', 'ujian') NOT NULL,
    nilai DECIMAL(5,2) NOT NULL CHECK (nilai >= 0 AND nilai <= 100),
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pelaksanaan_sidang_id) REFERENCES pelaksanaan_sidang(id) ON DELETE CASCADE,
    FOREIGN KEY (dosen_id) REFERENCES dosen(id) ON DELETE CASCADE,
    UNIQUE KEY unique_nilai (pelaksanaan_sidang_id, dosen_id, jenis_nilai)
);
```

