# Panduan Kompilasi Laporan LaTeX SISRI-BPI

## Struktur File

```
docs/
├── laporan-main.tex              # File utama (main document)
├── laporan-bab4-analisis-perancangan.tex   # BAB 4
├── laporan-bab5-implementasi.tex           # BAB 5
├── images/                        # Folder untuk gambar
│   ├── logo-utm.png
│   ├── login.png
│   ├── dashboard-mahasiswa.png
│   ├── bimbingan.png
│   ├── jadwal-sidang.png
│   └── input-nilai.png
└── README.md                      # File ini
```

## Cara Menggunakan di Overleaf

### 1. Upload ke Overleaf
1. Buka [Overleaf](https://www.overleaf.com)
2. Klik **New Project** → **Upload Project**
3. Upload semua file `.tex` dan folder `images`

### 2. Struktur Project di Overleaf
```
Main document: laporan-main.tex
```

### 3. Kompilasi
- Pilih compiler: **pdfLaTeX**
- Klik tombol **Recompile**

## Package yang Diperlukan

Semua package sudah tersedia di Overleaf secara default:
- `tikz` - untuk diagram
- `pgf-umlsd` - untuk sequence diagram
- `listings` - untuk code listing
- `graphicx` - untuk gambar
- `hyperref` - untuk hyperlink
- `geometry` - untuk margin
- `fancyhdr` - untuk header/footer

## Menyesuaikan Konten

### Mengubah Data Penulis
Edit di `laporan-main.tex`:
```latex
\newcommand{\penulis}{[Nama Mahasiswa]}
\newcommand{\nim}{[NIM]}
```

### Menambah Screenshot
1. Ambil screenshot dari aplikasi SISRI-BPI
2. Simpan di folder `images/`
3. Referensi di dokumen:
```latex
\includegraphics[width=0.9\textwidth]{images/nama-file.png}
```

## Fitur Diagram

### Use Case Diagram
Menggunakan TikZ dengan custom styles

### Activity Diagram
Menggunakan TikZ flowchart styles

### Sequence Diagram
Menggunakan package `pgf-umlsd`

### Class Diagram
Menggunakan TikZ dengan custom class styles

### ERD
Menggunakan TikZ dengan entity-relationship styles

## Tips

1. **Gambar tidak muncul?**
   - Pastikan path gambar benar
   - Gunakan format PNG atau PDF

2. **Error kompilasi?**
   - Cek syntax LaTeX
   - Pastikan semua package ter-load

3. **Diagram tidak render?**
   - Pastikan TikZ library di-load
   - Cek syntax tikzpicture

## Kontak

Untuk pertanyaan terkait template ini, hubungi:
- Email: [email]
- GitHub: https://github.com/AchmadLutfi196/SISRI-BPI
