# KONSEP APLIKASI DAN LOGIC SISTEM INFORMASI SKRIPSI (SISRI)

## DAFTAR ISI
1. [Konsep Umum Sistem](#1-konsep-umum-sistem)
2. [Arsitektur Aplikasi](#2-arsitektur-aplikasi)
3. [Logic Flow Setiap Role](#3-logic-flow-setiap-role)
4. [State Management & Status Flow](#4-state-management--status-flow)
5. [Business Rules & Validasi](#5-business-rules--validasi)
6. [Notification System](#6-notification-system)
7. [File Management System](#7-file-management-system)

---

## 1. KONSEP UMUM SISTEM

### 1.1 Filosofi Sistem
SISRI adalah sistem workflow management yang mengotomatisasi proses skripsi mahasiswa dengan prinsip:
- **Sequential Process**: Setiap tahap harus diselesaikan sebelum lanjut ke tahap berikutnya
- **Multi-Approval System**: Setiap keputusan penting memerlukan persetujuan dari beberapa pihak
- **Audit Trail**: Setiap aktivitas tercatat dengan timestamp dan actor
- **Role-Based Access**: Setiap user hanya bisa akses fitur sesuai role-nya

### 1.2 Hierarki Pengguna
```
Admin UTM (Super Admin)
    ├── View semua data
    ├── Manage user accounts
    └── Reset passwords

Koordinator Prodi (Manager + Dosen)
    ├── Akses semua fitur Dosen
    ├── Manage bidang minat
    ├── Approve/reject pendaftaran sidang
    ├── Scheduling sidang
    └── View rekap nilai

Dosen (Executor)
    ├── Pembimbing: Guide mahasiswa
    └── Penguji: Evaluate mahasiswa

Mahasiswa (End User)
    └── Submit & follow workflow
```

---

## 2. ARSITEKTUR APLIKASI

### 2.1 Konsep MVC dalam SISRI

```
REQUEST (User Action)
    ↓
ROUTE (web.php)
    ↓
MIDDLEWARE (Auth, Role Check)
    ↓
CONTROLLER (Handle Request)
    ↓
SERVICE LAYER (Business Logic) ←→ NOTIFICATION SERVICE
    ↓                                      ↓
MODEL (Database Interaction)          EMAIL/IN-APP
    ↓
VIEW (Blade Template + Tailwind)
    ↓
RESPONSE (HTML/JSON/File)
```

### 2.2 Layer Responsibility

**Controller Layer**
- Terima input dari user
- Validasi input (Form Request)
- Panggil service untuk business logic
- Return response (view/redirect/json)

**Service Layer**
- Implementasi business logic
- Koordinasi antar model
- Handle transaction
- Trigger notification
- Generate documents

**Model Layer**
- Define relationships
- Query scopes
- Accessors/Mutators
- Model events

**View Layer**
- Display data
- Form inputs
- User interactions
- Client-side validation

---

## 3. LOGIC FLOW SETIAP ROLE

### 3.1 ADMIN UTM - Logic Flow

**Konsep**: Admin adalah observer dan maintainer, tidak terlibat dalam workflow skripsi.

#### A. View Hierarchical Data
```
Logic Flow:
1. User klik menu "Data Mahasiswa/Dosen"
2. System load level 1: FAKULTAS
3. User klik "Detail" pada fakultas tertentu
4. System load level 2: JURUSAN (filtered by fakultas)
5. User klik "Detail" pada jurusan tertentu
6. System load level 3: PRODI (filtered by jurusan)
7. User klik "Detail" pada prodi tertentu
8. System load level 4: ANGKATAN (untuk mahasiswa) atau DOSEN (untuk dosen)
9. User klik "Data Mahasiswa/Dosen"
10. System tampilkan list dengan data lengkap

Technical Implementation:
- Use route parameters: /admin/mahasiswa/{fakultas_id}/{jurusan_id}/{prodi_id}
- Each level query filtered by parent_id
- Breadcrumb untuk navigasi
- Cache query untuk performance
```

#### B. Manage Koordinator Prodi
```
Logic Flow CREATE:
1. Admin pilih Prodi dari dropdown
2. System load dosen yang belum jadi koordinator di prodi tsb
3. Admin pilih Dosen dari dropdown
4. Admin klik "Tambah"
5. System validate:
   - Apakah prodi sudah punya koordinator aktif? → Error
   - Apakah dosen sudah koordinator di prodi lain? → Warning (boleh lanjut)
6. System insert ke tabel koordinator_prodi
7. System set is_active = true
8. System redirect dengan success message

Logic Flow DELETE:
1. Admin klik "Hapus" pada koordinator
2. System tampilkan confirmation dialog
3. Admin konfirmasi
4. System set is_active = false (soft delete)
5. System redirect dengan success message

Technical Implementation:
- Unique constraint pada (prodi_id, is_active=true)
- Validation: hanya boleh 1 koordinator aktif per prodi
- Koordinator lama otomatis set is_active=false saat add new
```

#### C. Reset Password
```
Logic Flow:
1. Admin cari user (mahasiswa/dosen) via search/filter
2. Admin klik "Reset Password"
3. System generate random password atau set default (NIM/NIP)
4. System hash password
5. System update tabel users
6. System kirim email notifikasi ke user
7. System tampilkan success message dengan password baru

Technical Implementation:
- Hash password dengan bcrypt
- Log activity untuk audit
- Optional: force user change password on next login
```

---

### 3.2 MAHASISWA - Logic Flow

**Konsep**: Mahasiswa follow sequential workflow dengan approval gates.

#### A. Workflow: Ajukan Topik + Pembimbing

```
┌─────────────────────────────────────────────────────────────┐
│ PHASE 1: PENGAJUAN TOPIK                                    │
└─────────────────────────────────────────────────────────────┘

Logic Flow:
1. Mahasiswa login
2. System cek: Apakah mahasiswa sudah punya topik aktif?
   - YES → Redirect ke dashboard, disable form ajukan topik
   - NO → Show form ajukan topik

3. Mahasiswa fill form:
   - Pilih Bidang Minat (dropdown dari bidang minat aktif di prodi)
   - Input Judul (textarea, max 500 char)
   - Upload File Proposal (PDF, max 10MB)

4. Mahasiswa klik "Simpan Topik"

5. System validate:
   - Bidang minat exists & is_active = true?
   - Judul tidak kosong?
   - File uploaded & valid format?

6. System process:
   - Upload file ke storage/proposals/{nim}/{timestamp}_proposal.pdf
   - Insert ke tabel topik_skripsi (status: 'menunggu')
   - Return topik_id

7. System tampilkan form Pilih Pembimbing

┌─────────────────────────────────────────────────────────────┐
│ PHASE 2: PILIH PEMBIMBING                                   │
└─────────────────────────────────────────────────────────────┘

8. System load dosen dari prodi mahasiswa
   - Filter: dosen aktif, bisa jadi pembimbing
   - Group by bidang minat (optional)

9. Mahasiswa pilih:
   - Pembimbing 1 (required)
   - Pembimbing 2 (required)

10. System validate:
    - Pembimbing 1 ≠ Pembimbing 2?
    - Both dosen exists?

11. System process:
    - Insert usulan_pembimbing (urutan: 1, status: 'menunggu')
    - Insert usulan_pembimbing (urutan: 2, status: 'menunggu')
    - Update topik status → 'menunggu_pembimbing'

12. System trigger notification:
    - Email ke Pembimbing 1
    - Email ke Pembimbing 2
    - In-app notification

13. System redirect ke halaman "Status Pengajuan"

Technical Implementation:
- Use DB transaction untuk atomicity
- Validate file upload dengan Laravel validation
- Store file path relatif di database
- Rollback if any step fails
```

#### B. Workflow: Bimbingan Proposal/Skripsi

```
┌─────────────────────────────────────────────────────────────┐
│ PREREQUISITES CHECK                                         │
└─────────────────────────────────────────────────────────────┘

System cek sebelum mahasiswa bisa akses halaman bimbingan:
1. Topik status = 'diterima'?
2. Kedua pembimbing sudah approve (status = 'diterima')?
3. Jika fase skripsi: Seminar proposal sudah selesai?

Jika salah satu kondisi tidak terpenuhi → Redirect dengan error message

┌─────────────────────────────────────────────────────────────┐
│ DISPLAY BIMBINGAN SHEET                                     │
└─────────────────────────────────────────────────────────────┘

View Structure:
- 2 kolom: Pembimbing 1 | Pembimbing 2
- Setiap kolom show list bimbingan (newest first)
- Each bimbingan card show:
  - Tanggal bimbingan
  - Pokok bimbingan
  - File mahasiswa (download link)
  - Status badge (menunggu/direvisi/disetujui)
  - Pesan dosen (if any)
  - File revisi dosen (if any)

┌─────────────────────────────────────────────────────────────┐
│ SUBMIT BIMBINGAN                                            │
└─────────────────────────────────────────────────────────────┘

Logic Flow:
1. Mahasiswa klik icon "pesawat" (submit bimbingan)
2. System tampilkan modal popup dengan form:
   - Pilih dosen (pembimbing 1 atau 2)
   - Pokok bimbingan (textarea, required)
   - Upload file (optional, PDF/DOCX, max 10MB)
   - Pesan/Keterangan (textarea, optional)

3. Mahasiswa fill & klik "Submit"

4. System validate:
   - Dosen exists & is pembimbing mahasiswa?
   - Pokok bimbingan tidak kosong?
   - File valid (if uploaded)?

5. System process:
   - Upload file ke storage/bimbingan/{topik_id}/{jenis}/{timestamp}_file.pdf
   - Insert ke tabel bimbingan:
     * topik_id
     * dosen_id
     * jenis ('proposal' or 'skripsi')
     * pokok_bimbingan
     * file_bimbingan (path)
     * pesan_mahasiswa
     * status: 'menunggu'
     * tanggal_bimbingan: now()

6. System trigger notification:
   - Email ke dosen terpilih
   - In-app notification
   - Update counter "bimbingan pending" di dashboard dosen

7. System close modal & refresh bimbingan list
8. System show success toast notification

┌─────────────────────────────────────────────────────────────┐
│ RECEIVE FEEDBACK FROM DOSEN                                 │
└─────────────────────────────────────────────────────────────┘

Logic Flow:
1. Dosen beri feedback (di sisi dosen, explained later)
2. System update bimbingan record:
   - pesan_dosen
   - file_revisi (if uploaded)
   - status: 'direvisi' or 'disetujui'
   - tanggal_respon: now()

3. System trigger notification ke mahasiswa:
   - Email
   - In-app notification

4. Mahasiswa refresh halaman
5. System tampilkan badge status terbaru
6. Mahasiswa bisa download file revisi (if any)

Technical Implementation:
- Ajax untuk submit form tanpa reload
- Real-time update dengan polling atau WebSocket (optional)
- Counter minimum bimbingan sebelum boleh daftar sidang
- Disable button submit jika masih ada bimbingan status 'menunggu'
```

#### C. Workflow: Daftar Seminar Proposal / Sidang Skripsi

```
┌─────────────────────────────────────────────────────────────┐
│ PREREQUISITES CHECK                                         │
└─────────────────────────────────────────────────────────────┘

System cek sebelum mahasiswa bisa daftar:

For Seminar Proposal:
1. Topik status = 'diterima'
2. Minimum bimbingan proposal terpenuhi (misal: min 8x)
3. Semua bimbingan proposal status = 'disetujui'
4. Belum pernah daftar seminar proposal atau status sebelumnya = 'ditolak'
5. Ada jadwal pendaftaran seminar proposal yang aktif (tanggal sekarang antara tanggal_buka dan tanggal_tutup)

For Sidang Skripsi:
1. Seminar proposal sudah selesai & revisi approved
2. Minimum bimbingan skripsi terpenuhi (misal: min 10x)
3. Semua bimbingan skripsi status = 'disetujui'
4. Belum pernah daftar sidang skripsi atau status sebelumnya = 'ditolak'
5. Ada jadwal pendaftaran sidang skripsi yang aktif

Jika salah satu tidak terpenuhi → Disable button daftar & show reason

┌─────────────────────────────────────────────────────────────┐
│ SUBMIT PENDAFTARAN                                          │
└─────────────────────────────────────────────────────────────┘

Logic Flow:
1. Mahasiswa klik "Daftar Seminar/Sidang"
2. System tampilkan confirmation dialog:
   - Pastikan semua bimbingan sudah selesai
   - Konfirmasi akan submit ke pembimbing & koordinator

3. Mahasiswa klik "Ya, Daftar"

4. System validate prerequisites (double-check server-side)

5. System process:
   - Insert ke tabel pendaftaran_sidang:
     * topik_id
     * jadwal_sidang_id (periode aktif)
     * jenis ('seminar_proposal' or 'sidang_skripsi')
     * status_pembimbing_1: 'menunggu'
     * status_pembimbing_2: 'menunggu'
     * status_koordinator: 'menunggu'
     * created_at: now()

6. System trigger notification:
   - Email ke Pembimbing 1
   - Email ke Pembimbing 2
   - Email ke Koordinator Prodi
   - In-app notification ke ketiganya

7. System update UI:
   - Disable button "Daftar"
   - Show approval tracker (3 checkbox: P1, P2, Koordinator)
   - Show status badge

8. System redirect ke halaman status dengan success message

┌─────────────────────────────────────────────────────────────┐
│ APPROVAL TRACKING                                           │
└─────────────────────────────────────────────────────────────┘

Display Logic:
- Show 3 cards/sections:
  1. Pembimbing 1: [Status Badge] [Tanggal Respon] [Catatan]
  2. Pembimbing 2: [Status Badge] [Tanggal Respon] [Catatan]
  3. Koordinator: [Status Badge] [Tanggal Respon] [Catatan]

- Status badge colors:
  * Menunggu: Yellow/Warning
  * Disetujui: Green/Success
  * Ditolak: Red/Danger

- Jika ada yang reject → Show "Pendaftaran Ditolak" + alasan
- Jika semua approve → Show "Menunggu Penjadwalan oleh Koordinator"

┌─────────────────────────────────────────────────────────────┐
│ LIHAT JADWAL SIDANG                                         │
└─────────────────────────────────────────────────────────────┘

Logic Flow:
1. Koordinator jadwalkan sidang (explained in Koordinator section)
2. System insert ke tabel pelaksanaan_sidang
3. System trigger notification ke mahasiswa
4. Mahasiswa refresh halaman
5. System tampilkan card "Jadwal Sidang":
   - Tanggal & Waktu
   - Tempat/Ruangan
   - Dosen Penguji 1, 2, 3
   - Button download "Undangan Sidang" (PDF)

Technical Implementation:
- Status pendaftaran disimpan dalam 3 kolom terpisah untuk tracking granular
- Use query scope untuk cek prerequisites
- Middleware untuk restrict access halaman daftar
- Generate jadwal card dari view composer
```

#### D. Workflow: Revisi Pasca Sidang

```
┌─────────────────────────────────────────────────────────────┐
│ PREREQUISITES CHECK                                         │
└─────────────────────────────────────────────────────────────┘

System cek:
1. Sidang sudah dilaksanakan (pelaksanaan_sidang.status = 'selesai')
2. Dosen penguji sudah input catatan revisi
3. Mahasiswa belum submit revisi atau status revisi = 'revisi_ulang'

┌─────────────────────────────────────────────────────────────┐
│ DISPLAY REVISI REQUIREMENTS                                │
└─────────────────────────────────────────────────────────────┘

View Structure:
- Show 3 sections untuk penguji 1, 2, 3
- Each section show:
  - Nama dosen penguji
  - Catatan revisi dari dosen
  - Form upload file revisi
  - Status validasi (if already submitted)

┌─────────────────────────────────────────────────────────────┐
│ SUBMIT REVISI                                               │
└─────────────────────────────────────────────────────────────┘

Logic Flow:
1. Mahasiswa upload file revisi per penguji
2. Mahasiswa isi catatan (optional)
3. Mahasiswa klik "Submit Revisi"

4. System validate:
   - File uploaded untuk semua penguji yang beri catatan?
   - File valid format & size?

5. System process:
   - Upload files ke storage/revisi/{pelaksanaan_id}/{penguji_id}/
   - Insert/Update tabel revisi_sidang untuk each penguji:
     * pelaksanaan_sidang_id
     * dosen_id (penguji)
     * file_revisi
     * catatan (from mahasiswa)
     * status: 'menunggu'
     * tanggal_submit: now()

6. System trigger notification:
   - Email ke masing-masing penguji
   - In-app notification

7. System redirect dengan success message

┌─────────────────────────────────────────────────────────────┐
│ TRACK VALIDATION STATUS                                    │
└─────────────────────────────────────────────────────────────┘

Display Logic:
- Show status per penguji:
  * Menunggu Validasi (yellow)
  * Disetujui (green)
  * Revisi Ulang (red) → bisa submit lagi

- Jika semua penguji approve → Show "Revisi Selesai, Menunggu Nilai Akhir"

Technical Implementation:
- One revisi record per penguji
- Support multiple submission (if revisi_ulang)
- Version control file revisi dengan timestamp
- Disable submit button if status = 'menunggu'
```

---

### 3.3 DOSEN - Logic Flow

**Konsep**: Dosen memiliki 2 peran: Pembimbing dan Penguji, dengan fitur berbeda.

#### A. Workflow: Validasi Usulan Pembimbing

```
┌─────────────────────────────────────────────────────────────┐
│ VIEW USULAN MAHASISWA                                       │
└─────────────────────────────────────────────────────────────┘

Logic Flow:
1. Dosen login & akses menu "Validasi Usulan"
2. System query usulan_pembimbing WHERE dosen_id = current dosen
3. System group by status:
   - Tab "Menunggu" (status = 'menunggu')
   - Tab "Diterima" (status = 'diterima')
   - Tab "Ditolak" (status = 'ditolak')

Display per usulan:
- Foto mahasiswa
- Nama & NIM
- Bidang minat
- Judul topik
- Link download proposal
- Urutan (Pembimbing 1 atau 2)
- Tanggal pengajuan

┌─────────────────────────────────────────────────────────────┐
│ RESPOND TO USULAN                                           │
└─────────────────────────────────────────────────────────────┘

Logic Flow TERIMA:
1. Dosen klik "Terima" pada usulan
2. System tampilkan modal:
   - Input jangka waktu bimbingan (date picker)
   - Textarea catatan (optional)

3. Dosen fill & klik "Simpan"

4. System validate:
   - Jangka waktu tidak boleh kurang dari hari ini
   - Jangka waktu reasonable (misal: max 2 tahun dari sekarang)

5. System process:
   - Update usulan_pembimbing:
     * status: 'diterima'
     * jangka_waktu: input
     * catatan: input
     * tanggal_respon: now()

6. System cek: Apakah semua pembimbing sudah terima?
   - Query usulan_pembimbing WHERE topik_id = X
   - Count status = 'diterima'
   - Jika count = 2 (both pembimbing) → Update topik_skripsi.status = 'diterima'

7. System trigger notification:
   - Email ke mahasiswa (usulan diterima)
   - Jika kedua pembimbing approve → Email konfirmasi "Topik disetujui, bisa mulai bimbingan"

8. System refresh page & show success message

Logic Flow TOLAK:
1. Dosen klik "Tolak"
2. System tampilkan modal:
   - Textarea alasan penolakan (required)

3. Dosen fill & klik "Simpan"

4. System validate:
   - Alasan tidak kosong

5. System process:
   - Update usulan_pembimbing:
     * status: 'ditolak'
     * catatan: alasan
     * tanggal_respon: now()

6. System logic:
   - TIDAK update topik status (mahasiswa bisa cari pembimbing lain)
   - Mahasiswa bisa ajukan dosen pengganti

7. System trigger notification:
   - Email ke mahasiswa (usulan ditolak + alasan)

8. System refresh page & show success message

Technical Implementation:
- Soft validation di client (datepicker constraint)
- Hard validation di server (Form Request)
- Atomic update dengan DB transaction
- Event listener untuk auto-update topik status
```

#### B. Workflow: Bimbingan Proposal/Skripsi (Pembimbing)

```
┌─────────────────────────────────────────────────────────────┐
│ VIEW MAHASISWA BIMBINGAN                                    │
└─────────────────────────────────────────────────────────────┘

Logic Flow:
1. Dosen akses menu "Bimbingan Proposal" atau "Bimbingan Skripsi"
2. System query mahasiswa yang dosen adalah pembimbing:
   - FROM topik_skripsi
   - JOIN usulan_pembimbing (WHERE dosen_id = current & status = 'diterima')
   - JOIN mahasiswa

3. System tampilkan tabel mahasiswa:
   - Nama & NIM
   - Judul topik
   - Jumlah bimbingan
   - Bimbingan pending (status = 'menunggu')
   - Last bimbingan date
   - Action: "Lihat Bimbingan"

┌─────────────────────────────────────────────────────────────┐
│ VIEW BIMBINGAN SHEET MAHASISWA                              │
└─────────────────────────────────────────────────────────────┘

Logic Flow:
1. Dosen klik "Lihat Bimbingan" pada mahasiswa tertentu
2. System query bimbingan:
   - WHERE topik_id = X
   - AND dosen_id = current dosen
   - AND jenis = 'proposal' or 'skripsi'
   - ORDER BY tanggal_bimbingan DESC

3. System tampilkan list bimbingan (timeline format):
   - Tanggal bimbingan
   - Pokok bimbingan
   - File mahasiswa (download link)
   - Pesan mahasiswa
   - Status badge
   - Response section (if dosen sudah respond):
     * Pesan dosen
     * File revisi dosen
   - Action buttons (if status = 'menunggu'):
     * Button "Beri Feedback"

┌─────────────────────────────────────────────────────────────┐
│ GIVE FEEDBACK TO MAHASISWA                                  │
└─────────────────────────────────────────────────────────────┘

Logic Flow:
1. Dosen klik "Beri Feedback" pada bimbingan pending
2. System tampilkan modal:
   - Textarea pesan/feedback (required)
   - File upload revisi (optional, PDF/DOCX)
   - Radio button status:
     * Direvisi (mahasiswa harus revisi lagi)
     * Disetujui (bimbingan accepted)

3. Dosen fill & klik "Simpan"

4. System validate:
   - Pesan tidak kosong
   - File valid (if uploaded)
   - Status selected

5. System process:
   - Upload file ke storage/bimbingan/revisi/{bimbingan_id}/
   - Update tabel bimbingan:
     * pesan_dosen: input
     * file_revisi: path (if uploaded)
     * status: 'direvisi' or 'disetujui'
     * tanggal_respon: now()

6. System trigger notification:
   - Email ke mahasiswa
   - In-app notification
   - Update dashboard counter

7. System close modal & refresh bimbingan list
8. System show success toast

Technical Implementation:
- Use ajax untuk submit tanpa reload page
- Real-time notification dengan Pusher/Laravel Echo (optional)
- Track jumlah bimbingan untuk requirement daftar sidang
- Badge counter di sidebar menu untuk bimbingan pending
```

#### C. Workflow: Validasi Daftar Seminar/Sidang (Pembimbing)

```
┌─────────────────────────────────────────────────────────────┐
│ VIEW PENDAFTARAN MAHASISWA                                  │
└─────────────────────────────────────────────────────────────┘

Logic Flow:
1. Dosen akses menu "Validasi Daftar Seminar" atau "Validasi Daftar Sidang"
2. System query pendaftaran yang membutuhkan validasi dosen:
   - FROM pendaftaran_sidang
   - JOIN topik_skripsi
   - JOIN usulan_pembimbing (WHERE dosen_id = current)
   - WHERE status_pembimbing_1 = 'menunggu' (jika dosen adalah pembimbing 1)
   - OR status_pembimbing_2 = 'menunggu' (jika dosen adalah pembimbing 2)

3. System tampilkan tabel:
   - Nama mahasiswa & NIM
   - Judul
   - Jenis sidang
   - Tanggal daftar
   - Jumlah bimbingan
   - Status bimbingan (all approved?)
   - Action: "Validasi"

┌─────────────────────────────────────────────────────────────┐
│ APPROVE/REJECT PENDAFTARAN                                  │
└─────────────────────────────────────────────────────────────┘

Logic Flow APPROVE:
1. Dosen klik "Setujui"
2. System tampilkan confirmation dialog (optional)
3. Dosen konfirmasi

4. System process:
   - Determine: Apakah dosen ini pembimbing 1 atau 2?
   - Update pendaftaran_sidang:
     * status_pembimbing_1 = 'disetujui' (if pembimbing 1)
     * OR status_pembimbing_2 = 'disetujui' (if pembimbing 2)
     * catatan_pembimbing_1/2 = optional message

5. System cek: Apakah sudah semua approve?
   - Jika P1, P2, Koordinator semua = 'disetujui'
   - Trigger event "ReadyForScheduling"

6. System trigger notification:
   - Email ke mahasiswa (status update)
   - Jika semua approve → Email "Menunggu penjadwalan"
   - Email ke koordinator (jika ini approval terakhir)

7. System refresh page & show success message

Logic Flow REJECT:
1. Dosen klik "Tolak"
2. System tampilkan modal:
   - Textarea alasan (required)

3. Dosen fill & klik "Simpan"

4. System validate:
   - Alasan tidak kosong

5. System process:
   - Update pendaftaran_sidang:
     * status_pembimbing_1/2 = 'ditolak'
     * catatan_pembimbing_1/2 = alasan
   - Mark keseluruhan pendaftaran sebagai ditolak

6. System trigger notification:
   - Email ke mahasiswa (penolakan + alasan)
   - Email ke pembimbing lain & koordinator (info)

7. System refresh page & show success message

Technical Implementation:
- Logic untuk determine urutan pembimbing (1 or 2)
- Conditional update based on role
- Transaction untuk ensure data consistency
- Business rule: Jika 1 reject, keseluruhan ditolak
```

#### D. Workflow: Berita Acara & Input Nilai (Pembimbing & Penguji)

```
┌─────────────────────────────────────────────────────────────┐
│ VIEW BERITA ACARA (SETELAH SIDANG DIJADWALKAN)            │
└─────────────────────────────────────────────────────────────┘

Logic Flow:
1. Dosen akses menu "Berita Acara Seminar" atau "Berita Acara Sidang"
2. System query pelaksanaan_sidang yang melibatkan dosen:
   - FROM pelaksanaan_sidang
   - JOIN penguji_sidang (WHERE dosen_id = current)
   - WHERE tanggal_sidang <= now() (sidang sudah lewat)

3. System tampilkan tabel:
   - Nama mahasiswa
   - Jenis sidang
   - Tanggal & tempat
   - Role dosen (Pembimbing 1/2 atau Penguji 1/2/3)
   - Status TTD berita acara
   - Action: "Lihat/TTD Berita Acara" | "Input Nilai"

┌─────────────────────────────────────────────────────────────┐
│ TANDA TANGAN BERITA ACARA                                   │
└─────────────────────────────────────────────────────────────┘

Logic Flow:
1. Dosen klik "Lihat/TTD Berita Acara"
2. System generate/load PDF berita acara:
   - Template berita acara dengan data:
     * Mahasiswa info
     * Judul
     * Tanggal & tempat sidang
     * Daftar pembimbing & penguji
     * Signature section

3. System tampilkan preview PDF
4. Dosen review & klik "Tanda Tangan"

5. System process:
   - Update penguji_sidang:
     * ttd_berita_acara = true
     * tanggal_ttd = now()
   - Append signature metadata ke PDF (optional)
   - Store signed PDF

6. System cek: Apakah semua dosen sudah TTD?
   - Query penguji_sidang WHERE pelaksanaan_sidang_id = X
   - Count ttd_berita_acara = true
   - Jika count = 5 (2 pembimbing + 3 penguji) → Mark BA as complete

7. System trigger notification:
   - Jika semua TTD → Email ke mahasiswa dengan BA final

8. System refresh page & show success message

┌─────────────────────────────────────────────────────────────┐
│ INPUT NILAI                                                 │
└─────────────────────────────────────────────────────────────┘

Logic Flow (PEMBIMBING):
1. Dosen pembimbing klik "Input Nilai"
2. System tampilkan form:
   - Section 1: Nilai Bimbingan (0-100)
     * Keaktifan
     * Kualitas revisi
     * Ketepatan waktu
   - Section 2: Nilai Ujian (0-100)
     * Penguasaan materi
     * Presentasi
     * Kemampuan menjawab

3. Dosen fill nilai & klik "Simpan"

4. System validate:
   - Semua nilai dalam range 0-100
   - Nilai tidak kosong

5. System process:
   - Insert/Update tabel nilai (2 records):
     * Record 1: jenis_nilai = 'bimbingan', nilai = avg(section 1)
     * Record 2: jenis_nilai = 'ujian', nilai = avg(section 2)
   - Both records: pelaksanaan_sidang_id, dosen_id

6. System trigger calculation (if all nilai sudah masuk):
   - Calculate nilai akhir mahasiswa
   - Update status sidang = 'selesai'

7. System refresh & show success message

Logic Flow (PENGUJI):
1. Dosen penguji klik "Input Nilai"
2. System tampilkan form:
   - Nilai Ujian (0-100)
     * Penguasaan materi
     * Presentasi
     * Kemampuan menjawab
     * Kualitas penulisan

3. Dosen fill & klik "Simpan"

4. System validate & process:
   - Insert nilai: jenis_nilai = 'ujian'

5. System trigger calculation (if all nilai masuk)

Technical Implementation:
- PDF generation dengan DomPDF
- Digital signature dengan certificate (optional) atau simple flag
- Nilai calculation dengan weighted average
- Business rule untuk bobot nilai (misal: bimbingan 30%, ujian 70%)
- Lock input nilai setelah finalisasi
```

#### E. Workflow: Validasi Revisi (Penguji)

```
┌─────────────────────────────────────────────────────────────┐
│ VIEW REVISI MAHASISWA                                       │
└─────────────────────────────────────────────────────────────┘

Logic Flow:
1. Dosen penguji akses menu "Validasi Revisi"
2. System query revisi yang perlu validasi:
   - FROM revisi_sidang
   - WHERE dosen_id = current
   - AND status = 'menunggu'
   - JOIN pelaksanaan_sidang → mahasiswa

3. System tampilkan tabel:
   - Nama mahasiswa
   - Jenis sidang
   - Catatan revisi yang dosen beri
   - File revisi mahasiswa (download link)
   - Tanggal submit
   - Action: "Validasi"

┌─────────────────────────────────────────────────────────────┐
│ VALIDATE REVISI                                             │
└─────────────────────────────────────────────────────────────┘

Logic Flow:
1. Dosen download & review file revisi
2. Dosen klik "Validasi"
3. System tampilkan modal:
   - Radio button:
     * Disetujui (revisi memenuhi syarat)
     * Revisi Ulang (mahasiswa harus revisi lagi)
   - Textarea catatan (required jika revisi ulang)

4. Dosen pilih & klik "Simpan"

5. System validate:
   - Status selected
   - Catatan tidak kosong (jika revisi ulang)

6. System process:
   - Update revisi_sidang:
     * status: 'disetujui' or 'revisi_ulang'
     * catatan: input (jika revisi ulang)
     * tanggal_validasi: now()

7. System cek: Apakah semua penguji sudah approve?
   - Query revisi_sidang WHERE pelaksanaan_sidang_id = X
   - Count status = 'disetujui'
   - Jika count = 3 (semua penguji) → Mark revisi as complete
   - Trigger finalisasi skripsi

8. System trigger notification:
   - Email ke mahasiswa (status revisi)
   - Jika semua approve → Congratulation email "Skripsi selesai"

9. System refresh & show success message

Technical Implementation:
- Support multiple revisi cycle (mahasiswa submit → reject → submit lagi)
- Version control untuk file revisi
- Final status agregat dari semua penguji
- Generate surat keterangan selesai revisi (PDF)
```

---

### 3.4 KOORDINATOR PRODI - Logic Flow

**Konsep**: Koordinator adalah manager dengan akses semua fitur Dosen + fitur manajemen.

#### A. Workflow: Manajemen Bidang Minat

```
┌─────────────────────────────────────────────────────────────┐
│ VIEW BIDANG MINAT                                           │
└─────────────────────────────────────────────────────────────┘

Logic Flow:
1. Koordinator akses menu "Manajemen Bidang Minat"
2. System query bidang_minat WHERE prodi_id = koordinator prodi
3. System tampilkan:
   - Form tambah bidang minat (top section)
   - Tabel data bidang minat (bottom section)

┌─────────────────────────────────────────────────────────────┐
│ CREATE BIDANG MINAT                                         │
└─────────────────────────────────────────────────────────────┘

Logic Flow:
1. Koordinator fill form:
   - Nama bidang minat (text input, required)
   - Deskripsi (textarea, optional)
   - Status (toggle: Aktif/Tidak Aktif)

2. Koordinator klik "Tambah"

3. System validate:
   - Nama tidak kosong
   - Nama tidak duplikat di prodi yang sama
   - Deskripsi max 1000 char

4. System process:
   - Insert ke tabel bidang_minat:
     * prodi_id: from koordinator
     * nama: input
     * deskripsi: input
     * is_active: from toggle

5. System refresh page & show success message
6. System update tabel bidang minat

┌─────────────────────────────────────────────────────────────┐
│ UPDATE BIDANG MINAT                                         │
└─────────────────────────────────────────────────────────────┘

Logic Flow:
1. Koordinator klik "Edit" pada bidang minat
2. System load data ke form atau modal
3. Koordinator ubah data & klik "Update"

4. System validate (same as create)

5. System process:
   - Update bidang_minat WHERE id = X

6. System cek: Jika is_active diubah ke false
   - Apakah ada topik mahasiswa yang masih aktif dengan bidang minat ini?
   - Jika ada → Warning "X mahasiswa masih menggunakan bidang minat ini"
   - Tetap update (soft deactivate, tidak mengganggu data existing)

7. System refresh & show success message

┌─────────────────────────────────────────────────────────────┐
│ DELETE BIDANG MINAT                                         │
└─────────────────────────────────────────────────────────────┘

Logic Flow:
1. Koordinator klik "Hapus"
2. System cek dependencies:
   - Apakah ada topik dengan bidang_minat_id ini?

3. System logic:
   - Jika ada dependencies → Error "Tidak bisa hapus, ada X topik"
   - Jika tidak ada → Show confirmation dialog

4. Koordinator konfirmasi
5. System delete bidang_minat WHERE id = X
6. System refresh & show success message

Technical Implementation:
- Soft delete dengan is_active flag (recommended)
- Hard delete hanya jika no dependencies
- Cascade handling untuk relasi
- Toggle aktif/non-aktif untuk temporary disable
```

#### B. Workflow: Validasi Perizinan Sidang

```
┌─────────────────────────────────────────────────────────────┐
│ VIEW PENDAFTARAN MAHASISWA                                  │
└─────────────────────────────────────────────────────────────┘

Logic Flow:
1. Koordinator akses menu "Validasi Perizinan Sidang"
2. System query pendaftaran yang perlu validasi koordinator:
   - FROM pendaftaran_sidang
   - JOIN topik_skripsi → mahasiswa → prodi
   - WHERE prodi_id = koordinator prodi
   - AND status_koordinator = 'menunggu'
   - Optional filter: jenis (seminar/sidang)

3. System tampilkan tabel:
   - Nama & NIM mahasiswa
   - Judul
   - Jenis sidang
   - Status pembimbing 1 & 2
   - Tanggal daftar
   - Action: "Validasi"

┌─────────────────────────────────────────────────────────────┐
│ APPROVE/REJECT PENDAFTARAN                                  │
└─────────────────────────────────────────────────────────────┘

Logic Flow APPROVE:
1. Koordinator klik "Setujui"
2. System tampilkan confirmation

3. System validate:
   - Pembimbing 1 & 2 sudah approve?
   - Jika belum → Error "Menunggu persetujuan pembimbing"

4. System process:
   - Update pendaftaran_sidang:
     * status_koordinator = 'disetujui'
     * catatan_koordinator = optional

5. System cek: Apakah semua sudah approve?
   - Jika P1, P2, Koordinator semua = 'disetujui'
   - Mark pendaftaran as "ReadyForScheduling"

6. System trigger notification:
   - Email ke mahasiswa
   - Email ke pembimbing (info)

7. System refresh & success message

Logic Flow REJECT:
1. Koordinator klik "Tolak"
2. System tampilkan modal dengan textarea alasan

3. Koordinator fill & submit

4. System process:
   - Update status_koordinator = 'ditolak'
   - Mark keseluruhan pendaftaran ditolak

5. System trigger notification ke all parties

Technical Implementation:
- Koordinator approval adalah final gate
- Business logic: Butuh semua approval sebelum bisa jadwalkan
- Rejection bersifat final (mahasiswa harus daftar ulang)
```

#### C. Workflow: Penjadwalan Sidang

```
┌─────────────────────────────────────────────────────────────┐
│ CREATE PERIODE PENDAFTARAN                                  │
└─────────────────────────────────────────────────────────────┘

Logic Flow:
1. Koordinator akses menu "Penjadwalan Sidang"
2. System tampilkan 2 sections:
   - Section 1: Form buat jadwal pendaftaran (top)
   - Section 2: Tabel jadwal & pendaftar (bottom)

3. Koordinator fill form:
   - Pilih periode akademik (dropdown)
   - Pilih jenis sidang (radio: Seminar Proposal / Sidang Skripsi)
   - Nama periode (text, contoh: "Seminar Proposal Periode Januari 2024")
   - Tanggal buka pendaftaran (datetime picker)
   - Tanggal tutup pendaftaran (datetime picker)

4. Koordinator klik "Buat Jadwal Pendaftaran"

5. System validate:
   - Tanggal buka < Tanggal tutup
   - Tanggal buka >= hari ini
   - Tidak overlap dengan jadwal lain di prodi yang sama

6. System process:
   - Insert ke tabel jadwal_sidang:
     * prodi_id: from koordinator
     * periode_id: selected
     * jenis: selected
     * nama_periode: input
     * tanggal_buka: input
     * tanggal_tutup: input
     * is_active: true

7. System refresh & show success message
8. System notifikasi ke mahasiswa: "Pendaftaran X dibuka"

┌─────────────────────────────────────────────────────────────┐
│ VIEW PENDAFTAR & ASSIGN JADWAL                              │
└─────────────────────────────────────────────────────────────┘

Logic Flow:
1. System tampilkan tabel jadwal pendaftaran yang sudah dibuat
2. Each row ada action "Lihat Pendaftar"
3. Koordinator klik "Lihat Pendaftar"

4. System query pendaftaran:
   - FROM pendaftaran_sidang
   - WHERE jadwal_sidang_id = X
   - AND semua approval = 'disetujui'
   - JOIN ke mahasiswa, topik

5. System tampilkan tabel pendaftar:
   - Nama & NIM
   - Judul
   - Status penjadwalan (Belum Dijadwalkan / Sudah Dijadwalkan)
   - Action: "Jadwalkan Sidang"

┌─────────────────────────────────────────────────────────────┐
│ SCHEDULE SIDANG EXECUTION                                   │
└─────────────────────────────────────────────────────────────┘

Logic Flow:
1. Koordinator klik "Jadwalkan Sidang" pada mahasiswa
2. System tampilkan modal form:
   - Tanggal & waktu sidang (datetime picker)
   - Tempat/Ruangan (text input)
   - Pilih Penguji 1 (dropdown dosen)
   - Pilih Penguji 2 (dropdown dosen)
   - Pilih Penguji 3 (dropdown dosen)

3. Koordinator fill & klik "Simpan Jadwal"

4. System validate:
   - Tanggal sidang > tanggal sekarang
   - Tanggal dalam range reasonable (misal: max 3 bulan dari sekarang)
   - Penguji 1, 2, 3 berbeda satu sama lain
   - Penguji tidak sama dengan pembimbing (optional rule)
   - Penguji belum ada jadwal bentrok di waktu yang sama

5. System process:
   - Insert ke tabel pelaksanaan_sidang:
     * pendaftaran_sidang_id: X
     * tanggal_sidang: input
     * tempat: input
     * status: 'dijadwalkan'
   - Get pelaksanaan_sidang_id

6. System assign dosen (5 insert):
   - Get pembimbing 1 & 2 dari topik
   - Insert penguji_sidang:
     * pelaksanaan_sidang_id
     * dosen_id: pembimbing 1
     * role: 'pembimbing_1'
   - Insert untuk pembimbing 2
   - Insert untuk penguji 1, 2, 3 (role: 'penguji_1', 'penguji_2', 'penguji_3')

7. System generate undangan sidang (PDF):
   - Template dengan data lengkap sidang
   - Save ke storage
   - Link ke pelaksanaan_sidang.berita_acara

8. System trigger notification:
   - Email ke mahasiswa (jadwal + undangan PDF)
   - Email ke 5 dosen (pembimbing + penguji)
   - In-app notification

9. System close modal & refresh tabel
10. System update status penjadwalan mahasiswa

┌─────────────────────────────────────────────────────────────┐
│ EDIT JADWAL SIDANG                                          │
└─────────────────────────────────────────────────────────────┘

Logic Flow:
1. Koordinator klik "Edit Jadwal" pada mahasiswa yang sudah dijadwalkan
2. System load data existing ke form
3. Koordinator ubah data & klik "Update"

4. System validate (same as create)

5. System process:
   - Update pelaksanaan_sidang
   - Update penguji_sidang (jika penguji berubah)

6. System logic:
   - Jika tanggal/waktu berubah → Cek konflik dosen lain
   - Jika penguji berubah → Delete old, insert new record

7. System trigger notification:
   - Email ke all parties tentang perubahan jadwal

8. System refresh & success message

Technical Implementation:
- Conflict detection untuk jadwal dosen
- Validation rule untuk penguji uniqueness
- Auto-generate undangan dengan template
- Support reschedule dengan limit (misal: max 2x reschedule)
- Lock jadwal setelah H-3 (prevent last-minute change)
```

#### D. Workflow: Daftar Nilai Mahasiswa

```
┌─────────────────────────────────────────────────────────────┐
│ VIEW REKAP NILAI                                            │
└─────────────────────────────────────────────────────────────┘

Logic Flow:
1. Koordinator akses menu "Daftar Nilai"
2. System tampilkan filter:
   - Periode akademik
   - Jenis sidang
   - Angkatan mahasiswa

3. Koordinator pilih filter & klik "Tampilkan"

4. System query:
   - FROM pelaksanaan_sidang
   - JOIN pendaftaran_sidang → topik → mahasiswa
   - JOIN nilai (aggregate)
   - WHERE prodi = koordinator prodi
   - AND filter conditions

5. System tampilkan tabel:
   - NIM & Nama
   - Judul
   - Jenis sidang
   - Tanggal sidang
   - Nilai Bimbingan (avg dari 2 pembimbing)
   - Nilai Ujian (avg dari 2 pembimbing + 3 penguji)
   - Nilai Akhir (weighted average)
   - Grade (A/B/C/D/E)
   - Status Revisi
   - Action: "Detail Nilai" | "Export PDF"

┌─────────────────────────────────────────────────────────────┐
│ VIEW DETAIL NILAI MAHASISWA                                 │
└─────────────────────────────────────────────────────────────┘

Logic Flow:
1. Koordinator klik "Detail Nilai"
2. System query nilai breakdown:
   - Nilai bimbingan dari Pembimbing 1 & 2
   - Nilai ujian dari Pembimbing 1, 2, Penguji 1, 2, 3

3. System tampilkan modal/page:
   - Section Nilai Bimbingan:
     * Pembimbing 1: [nilai] (tanggal input)
     * Pembimbing 2: [nilai] (tanggal input)
     * Rata-rata: [calculated]
   - Section Nilai Ujian:
     * Pembimbing 1: [nilai]
     * Pembimbing 2: [nilai]
     * Penguji 1: [nilai]
     * Penguji 2: [nilai]
     * Penguji 3: [nilai]
     * Rata-rata: [calculated]
   - Nilai Akhir: [formula: bimbingan 30% + ujian 70%]
   - Grade: [conversion]

┌─────────────────────────────────────────────────────────────┐
│ EXPORT REKAP NILAI                                          │
└─────────────────────────────────────────────────────────────┘

Logic Flow:
1. Koordinator klik "Export Excel" atau "Export PDF"
2. System generate file:
   - Ambil data sesuai filter
   - Format dalam template Excel/PDF
   - Include metadata (periode, prodi, tanggal export)

3. System download file ke browser

Technical Implementation:
- Nilai calculation dengan query aggregate
- Grade conversion logic (configurable threshold)
- Excel export dengan Laravel Excel
- PDF export dengan DomPDF
- Cache query result untuk performance
```

---

## 4. STATE MANAGEMENT & STATUS FLOW

### 4.1 Status Flow Diagram

```
TOPIK SKRIPSI STATUS
─────────────────────────────────────────────────────────────
Initial: menunggu (after submission)
    ↓
menunggu_pembimbing (after pilih pembimbing)
    ↓
diterima (after both pembimbing approve)
    OR
ditolak (if any pembimbing reject) → END atau mahasiswa cari pengganti

USULAN PEMBIMBING STATUS
─────────────────────────────────────────────────────────────
Initial: menunggu
    ↓
diterima (dosen approve)
    OR
ditolak (dosen reject)

BIMBINGAN STATUS
─────────────────────────────────────────────────────────────
Initial: menunggu (after mahasiswa submit)
    ↓
direvisi (dosen beri feedback, mahasiswa harus revisi)
    OR
disetujui (dosen setujui, bimbingan accepted)

PENDAFTARAN SIDANG STATUS (COMPOSITE)
─────────────────────────────────────────────────────────────
3 Status terpisah:
- status_pembimbing_1: menunggu → disetujui/ditolak
- status_pembimbing_2: menunggu → disetujui/ditolak
- status_koordinator: menunggu → disetujui/ditolak

Overall Status (calculated):
- Pending: salah satu masih menunggu
- Approved: semua disetujui → Ready for scheduling
- Rejected: salah satu ditolak

PELAKSANAAN SIDANG STATUS
─────────────────────────────────────────────────────────────
Initial: dijadwalkan (after koordinator schedule)
    ↓
selesai (after sidang executed & nilai input)
    OR
dibatalkan (if need to cancel)

REVISI SIDANG STATUS
─────────────────────────────────────────────────────────────
Initial: menunggu (after mahasiswa submit)
    ↓
disetujui (penguji approve)
    OR
revisi_ulang (penguji reject, mahasiswa submit lagi) → loop back to menunggu
```

### 4.2 Conditional Logic Matrix

| Kondisi | Rule | Action |
|---------|------|--------|
| **Mahasiswa bisa ajukan topik?** | Belum punya topik aktif | Enable form |
| **Mahasiswa bisa bimbingan proposal?** | Topik status = 'diterima' AND kedua pembimbing approve | Enable menu |
| **Mahasiswa bisa daftar seminar?** | Min bimbingan proposal terpenuhi AND semua bimbingan 'disetujui' AND ada periode pendaftaran aktif | Enable button |
| **Dosen bisa validasi usulan?** | Usulan status = 'menunggu' | Show action button |
| **Koordinator bisa jadwalkan sidang?** | Semua approval = 'disetujui' | Show action button |
| **Dosen bisa input nilai?** | Sidang sudah dilaksanakan AND belum input nilai | Enable form |

---

## 5. BUSINESS RULES & VALIDASI

### 5.1 Validation Rules per Entity

#### Topik Skripsi
```
Create:
- bidang_minat_id: required, exists, is_active=true
- judul: required, string, max:500, unique per mahasiswa
- file_proposal: required, file, mimes:pdf, max:10240 (10MB)

Business Rules:
- Mahasiswa hanya boleh punya 1 topik aktif
- Jika topik ditolak, mahasiswa bisa ajukan topik baru
```

#### Usulan Pembimbing
```
Create:
- topik_id: required, exists
- dosen_id: required, exists, dosen aktif
- urutan: required, in:1,2

Business Rules:
- Pembimbing 1 ≠ Pembimbing 2
- Tidak boleh duplikat usulan (cek existing usulan dengan status != 'ditolak')
- Dosen bisa terima max X mahasiswa bimbingan per periode
```

#### Bimbingan
```
Create:
- topik_id: required, exists, status='diterima'
- dosen_id: required, is_pembimbing_of(topik)
- jenis: required, in:proposal,skripsi
- pokok_bimbingan: required, string
- file_bimbingan: nullable, file, mimes:pdf,docx,pptx, max:10240
- pesan_mahasiswa: nullable, string

Business Rules:
- Mahasiswa tidak bisa submit bimbingan baru jika ada bimbingan 'menunggu'
- Minimal interval bimbingan: 3 hari (prevent spam)
```

#### Pendaftaran Sidang
```
Create:
- topik_id: required, unique (per jenis sidang)
- jadwal_sidang_id: required, exists, is_active
- jenis: required, in:seminar_proposal,sidang_skripsi

Business Rules:
- For seminar: Min bimbingan proposal (misal: 8x) terpenuhi
- For sidang: Seminar selesai AND min bimbingan skripsi (misal: 10x)
- Tidak boleh daftar jika ada pendaftaran 'menunggu'
- Hanya bisa daftar dalam periode aktif (now between tanggal_buka and tanggal_tutup)
```

#### Pelaksanaan Sidang
```
Create:
- pendaftaran_sidang_id: required, exists, all approved
- tanggal_sidang: required, date, after:now, before:+3 months
- tempat: required, string, max:100

Business Rules:
- Penguji 1, 2, 3 harus berbeda
- Penguji tidak boleh sama dengan pembimbing (optional)
- Cek konflik jadwal dosen (tidak boleh overlap ±2 jam)
```

### 5.2 Authorization Rules

```
Admin UTM:
- Can view all data
- Can manage users (create, reset password)
- Cannot interfere with workflow (read-only for workflow data)

Mahasiswa:
- Can only access own data
- Can submit topik, bimbingan, pendaftaran
- Cannot edit after certain status (misal: tidak bisa edit topik setelah diterima)

Dosen:
- Can view own bimbingan mahasiswa
- Can validate own usulan
- Can input nilai for sidang where assigned
- Cannot edit nilai after finalized

Koordinator:
- Has all Dosen permissions
- Can manage bidang minat in own prodi
- Can validate pendaftaran in own prodi
- Can schedule sidang in own prodi
- Can view all nilai in own prodi
```

---

## 6. NOTIFICATION SYSTEM

### 6.1 Notification Triggers

| Event | Receiver | Channel | Content |
|-------|----------|---------|---------|
| **Topik diajukan** | Dosen (2 pembimbing) | Email | "Mahasiswa X mengajukan usulan pembimbingan" |
| **Usulan diterima/ditolak** | Mahasiswa | Email + In-app | Status usulan + catatan dosen |
| **Topik disetujui** | Mahasiswa | Email + In-app | "Topik disetujui, bisa mulai bimbingan" |
| **Bimbingan disubmit** | Dosen | Email + In-app | "Mahasiswa X submit bimbingan baru" |
| **Bimbingan direspon** | Mahasiswa | Email + In-app | Feedback dosen + file revisi |
| **Pendaftaran sidang** | Pembimbing (2) + Koordinator | Email + In-app | "Mahasiswa X daftar seminar/sidang" |
| **Pendaftaran disetujui** | Mahasiswa | Email + In-app | Status approval |
| **Pendaftaran ditolak** | Mahasiswa | Email + In-app | Alasan penolakan |
| **Sidang dijadwalkan** | Mahasiswa + 5 Dosen | Email + In-app | Jadwal + undangan PDF |
| **Revisi disubmit** | Penguji (3) | Email + In-app | "Mahasiswa X submit revisi" |
| **Revisi divalidasi** | Mahasiswa | Email + In-app | Status validasi |
| **Nilai diinput** | Mahasiswa (if all nilai done) | Email + In-app | "Nilai skripsi sudah keluar" |

### 6.2 Notification Implementation

```
Architecture:
Request → Controller → Service → Event Dispatched
                                      ↓
                              Event Listener
                                      ↓
                         ┌────────────┴────────────┐
                         ↓                         ↓
                   Email Queue              In-App Notification
                         ↓                         ↓
                   Send via SMTP         Store in DB (notifications table)
```

**Technical Stack:**
- Laravel Events & Listeners
- Laravel Queues (Redis/Database driver)
- Laravel Notifications (Email + Database channel)
- Optional: Pusher/Laravel Echo untuk real-time

---

## 7. FILE MANAGEMENT SYSTEM

### 7.1 File Storage Structure

```
storage/app/public/
├── proposals/
│   ├── {nim}/
│   │   └── {timestamp}_proposal.pdf
├── bimbingan/
│   ├── {topik_id}/
│   │   ├── proposal/
│   │   │   ├── mahasiswa/
│   │   │   │   └── {timestamp}_file.pdf
│   │   │   └── dosen/
│   │   │       └── {timestamp}_revisi.pdf
│   │   └── skripsi/
│   │       ├── mahasiswa/
│   │       └── dosen/
├── revisi/
│   ├── {pelaksanaan_sidang_id}/
│   │   ├── penguji_1/
│   │   │   └── {version}_{timestamp}_revisi.pdf
│   │   ├── penguji_2/
│   │   └── penguji_3/
├── berita_acara/
│   └── {pelaksanaan_sidang_id}_ba.pdf
└── undangan/
    └── {pelaksanaan_sidang_id}_undangan.pdf
```

### 7.2 File Handling Logic

```
Upload Process:
1. Validate file (type, size)
2. Generate unique filename: {timestamp}_{original_name}
3. Store to designated path
4. Save relative path to database
5. Return success/error

Download Process:
1. Check authorization (user can access file?)
2. Check file exists
3. Return download response with proper headers
4. Log download activity

Delete Process:
1. Check authorization
2. Soft delete record in database
3. Optional: Keep file (for audit) or hard delete after X days
4. Log deletion

Security:
- All uploads go through validation
- Store outside public directory (use symlink)
- Check authorization before download
- Sanitize filename (remove special chars)
- Virus scan (optional, with ClamAV)
```

---

## 8. PERFORMANCE OPTIMIZATION STRATEGIES

### 8.1 Database Optimization

```
Indexing Strategy:
- Primary keys (auto indexed)
- Foreign keys (manual index)
- Status columns (for filtering)
- Date columns (for date range queries)
- Composite index: (prodi_id, status) untuk query filtering

Query Optimization:
- Eager loading untuk relasi (N+1 problem)
- Select only needed columns
- Use query caching untuk data jarang berubah
- Pagination untuk list data
- Chunk untuk process large data

Example:
// Bad (N+1 problem)
$mahasiswa = Mahasiswa::all();
foreach($mahasiswa as $mhs) {
    echo $mhs->prodi->nama; // Query per loop
}

// Good (Eager loading)
$mahasiswa = Mahasiswa::with('prodi')->get();
foreach($mahasiswa as $mhs) {
    echo $mhs->prodi->nama; // No additional query
}
```

### 8.2 Caching Strategy

```
Cache Items:
- Bidang minat per prodi (jarang berubah)
- Daftar dosen per prodi (jarang berubah)
- Unit hierarchy (fakultas → jurusan → prodi)
- Periode akademik aktif
- User permissions

Cache Duration:
- Static data: 24 hours
- Semi-static data: 1 hour
- Dynamic data: 5-10 minutes

Cache Invalidation:
- Clear cache on data update (event listener)
- Schedule cache clear daily at midnight
- Manual cache clear command for admin

Example:
$bidangMinat = Cache::remember('bidang_minat_' . $prodi_id, 3600, function() use ($prodi_id) {
    return BidangMinat::where('prodi_id', $prodi_id)
        ->where('is_active', true)
        ->get();
});
```

### 8.3 Asset Optimization

```
Frontend:
- Minify CSS & JS (Vite build)
- Lazy load images
- Use CDN untuk libraries (optional)
- Compress images sebelum upload
- Use icon fonts (Lucide/Heroicons) instead of image icons

Backend:
- Enable OPcache untuk PHP
- Use Redis untuk session & cache
- Enable Gzip compression
- Optimize autoloader (composer dump-autoload -o)
```

---

## 9. SECURITY BEST PRACTICES

### 9.1 Input Validation & Sanitization

```
All user input MUST be validated:
- Use Form Request untuk complex validation
- Validate type, format, range
- Sanitize HTML input (strip tags)
- Escape output di Blade ({{ }} auto-escape)

File Upload Security:
- Validate MIME type (not just extension)
- Limit file size
- Rename file (remove original name)
- Store outside public directory
- Scan virus (optional)
```

### 9.2 Authentication & Authorization

```
Authentication:
- Password hash dengan bcrypt (default Laravel)
- Session-based auth dengan Laravel Breeze
- CSRF protection (automatic)
- Rate limiting login attempt (throttle middleware)

Authorization:
- Route middleware untuk role check
- Policy untuk resource authorization
- Gate untuk complex permission logic
- Check ownership sebelum update/delete

Example Middleware:
Route::middleware(['auth', 'role:mahasiswa'])->group(function() {
    // Routes untuk mahasiswa
});
```

### 9.3 SQL Injection Prevention

```
Always use Eloquent atau Query Builder:
// Safe (parameterized query)
User::where('username', $input)->first();

// Unsafe (vulnerable to SQL injection)
DB::select("SELECT * FROM users WHERE username = '$input'");

Use Parameter Binding:
// Safe
DB::table('users')
    ->where('email', $email)
    ->update(['password' => $password]);
```

---

## 10. ERROR HANDLING & LOGGING

### 10.1 Error Handling Strategy

```
Try-Catch Pattern:
try {
    DB::beginTransaction();
    
    // Business logic here
    $topik = TopikSkripsi::create($data);
    $usulan = UsulanPembimbing::create($data2);
    
    DB::commit();
    return redirect()->back()->with('success', 'Berhasil');
    
} catch (\Exception $e) {
    DB::rollBack();
    Log::error('Error create topik: ' . $e->getMessage());
    return redirect()->back()
        ->with('error', 'Terjadi kesalahan sistem')
        ->withInput();
}

User-Friendly Error Messages:
- Development: Show detailed error
- Production: Show generic error, log details
- Translate technical error ke bahasa user
```

### 10.2 Logging Strategy

```
Log Levels:
- ERROR: System errors, exceptions
- WARNING: Deprecated features, validation failures
- INFO: Important events (login, approval, nilai input)
- DEBUG: Detailed debug information (development only)

Log Format:
[timestamp] [level] [user_id] [action] [details]

Example:
Log::info('Topik diajukan', [
    'mahasiswa_id' => $mahasiswa->id,
    'topik_id' => $topik->id,
    'judul' => $topik->judul
]);

Log Storage:
- Daily log files (auto-rotate)
- Retention: 30 days
- Monitor critical errors dengan alert system
```

---

## KESIMPULAN

Dokumen ini menjelaskan konsep dan logic lengkap sistem SISRI:

1. **Workflow Sequential**: Mahasiswa follow step-by-step process dengan approval gates
2. **Multi-Role System**: 4 role dengan responsibility berbeda tapi saling terkait
3. **State Management**: Status yang jelas untuk tracking progress
4. **Notification System**: Real-time notification untuk keep all parties informed
5. **Security First**: Validation, authorization, dan security di setiap layer
6. **Performance Optimized**: Caching, indexing, query optimization
7. **Maintainable Code**: Clean architecture dengan separation of concerns

**Prinsip Pengembangan:**
- Start dengan MVP (core features first)
- Test setiap workflow secara menyeluruh
- Follow Laravel best practices
- Document code untuk maintainability
- Think scalability dari awal

Gunakan dokumen ini sebagai blueprint saat develop aplikasi. Setiap logic flow sudah dijelaskan step-by-step dengan validasi dan business rules yang jelas.
