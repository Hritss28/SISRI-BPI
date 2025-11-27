<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\JadwalSidang;
use App\Models\PelaksanaanSidang;
use App\Models\PendaftaranSidang;
use App\Models\PengujiSidang;
use App\Models\Periode;
use Illuminate\Http\Request;

class PenjadwalanController extends Controller
{
    private function getProdiId()
    {
        $dosen = auth()->user()->dosen;
        $koordinator = $dosen->koordinatorProdi()->active()->first();
        return $koordinator?->prodi_id;
    }

    public function index()
    {
        $prodiId = $this->getProdiId();

        if (!$prodiId) {
            return redirect()->route('dosen.dashboard')
                ->with('error', 'Anda tidak memiliki akses sebagai koordinator.');
        }

        $jadwals = JadwalSidang::where('prodi_id', $prodiId)
            ->with('periode')
            ->withCount('pendaftaranSidang')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('koordinator.penjadwalan.index', compact('jadwals'));
    }

    public function create()
    {
        $prodiId = $this->getProdiId();

        if (!$prodiId) {
            return redirect()->route('dosen.dashboard')
                ->with('error', 'Anda tidak memiliki akses sebagai koordinator.');
        }

        $periodes = Periode::orderBy('tahun_akademik', 'desc')->get();

        return view('koordinator.penjadwalan.create', compact('periodes'));
    }

    public function store(Request $request)
    {
        $prodiId = $this->getProdiId();

        if (!$prodiId) {
            return redirect()->route('dosen.dashboard')
                ->with('error', 'Anda tidak memiliki akses sebagai koordinator.');
        }

        $request->validate([
            'periode_id' => 'required|exists:periode,id',
            'jenis' => 'required|in:seminar_proposal,sidang_skripsi',
            'nama_periode' => 'required|max:100',
            'tanggal_buka' => 'required|date',
            'tanggal_tutup' => 'required|date|after:tanggal_buka',
            'is_active' => 'boolean',
        ]);

        JadwalSidang::create([
            'prodi_id' => $prodiId,
            'periode_id' => $request->periode_id,
            'jenis' => $request->jenis,
            'nama_periode' => $request->nama_periode,
            'tanggal_buka' => $request->tanggal_buka,
            'tanggal_tutup' => $request->tanggal_tutup,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('koordinator.penjadwalan.index')
            ->with('success', 'Jadwal sidang berhasil dibuat.');
    }

    public function show(JadwalSidang $jadwal)
    {
        $prodiId = $this->getProdiId();

        if (!$prodiId || $jadwal->prodi_id !== $prodiId) {
            abort(403);
        }

        $pendaftarans = PendaftaranSidang::where('jadwal_sidang_id', $jadwal->id)
            ->with(['topik.mahasiswa', 'pelaksanaanSidang'])
            ->paginate(15);

        return view('koordinator.penjadwalan.show', compact('jadwal', 'pendaftarans'));
    }

    public function approvePendaftaran(Request $request, PendaftaranSidang $pendaftaran)
    {
        $prodiId = $this->getProdiId();

        if (!$prodiId || $pendaftaran->jadwalSidang->prodi_id !== $prodiId) {
            abort(403);
        }

        $request->validate([
            'catatan_koordinator' => 'nullable',
        ]);

        $pendaftaran->update([
            'status_koordinator' => 'disetujui',
            'catatan_koordinator' => $request->catatan_koordinator,
        ]);

        return back()->with('success', 'Pendaftaran sidang berhasil disetujui.');
    }

    public function rejectPendaftaran(Request $request, PendaftaranSidang $pendaftaran)
    {
        $prodiId = $this->getProdiId();

        if (!$prodiId || $pendaftaran->jadwalSidang->prodi_id !== $prodiId) {
            abort(403);
        }

        $request->validate([
            'catatan_koordinator' => 'required',
        ]);

        $pendaftaran->update([
            'status_koordinator' => 'ditolak',
            'catatan_koordinator' => $request->catatan_koordinator,
        ]);

        return back()->with('success', 'Pendaftaran sidang berhasil ditolak.');
    }

    public function createPelaksanaan(PendaftaranSidang $pendaftaran)
    {
        $prodiId = $this->getProdiId();

        if (!$prodiId || $pendaftaran->jadwalSidang->prodi_id !== $prodiId) {
            abort(403);
        }

        if (!$pendaftaran->isFullyApproved()) {
            return back()->with('error', 'Pendaftaran belum disetujui oleh semua pihak.');
        }

        $dosens = Dosen::where('prodi_id', $prodiId)->get();

        return view('koordinator.penjadwalan.create-pelaksanaan', compact('pendaftaran', 'dosens'));
    }

    public function storePelaksanaan(Request $request, PendaftaranSidang $pendaftaran)
    {
        $prodiId = $this->getProdiId();

        if (!$prodiId || $pendaftaran->jadwalSidang->prodi_id !== $prodiId) {
            abort(403);
        }

        $request->validate([
            'tanggal_sidang' => 'required|date',
            'tempat' => 'required|max:100',
            'penguji_1_id' => 'required|exists:dosen,id',
            'penguji_2_id' => 'required|exists:dosen,id|different:penguji_1_id',
        ]);

        $pelaksanaan = PelaksanaanSidang::create([
            'pendaftaran_sidang_id' => $pendaftaran->id,
            'tanggal_sidang' => $request->tanggal_sidang,
            'tempat' => $request->tempat,
            'status' => 'dijadwalkan',
        ]);

        // Tambah pembimbing sebagai penguji
        $pembimbing1 = $pendaftaran->topik->usulanPembimbing()->where('urutan', 1)->approved()->first();
        $pembimbing2 = $pendaftaran->topik->usulanPembimbing()->where('urutan', 2)->approved()->first();

        if ($pembimbing1) {
            PengujiSidang::create([
                'pelaksanaan_sidang_id' => $pelaksanaan->id,
                'dosen_id' => $pembimbing1->dosen_id,
                'role' => 'pembimbing_1',
            ]);
        }

        if ($pembimbing2) {
            PengujiSidang::create([
                'pelaksanaan_sidang_id' => $pelaksanaan->id,
                'dosen_id' => $pembimbing2->dosen_id,
                'role' => 'pembimbing_2',
            ]);
        }

        // Tambah penguji
        PengujiSidang::create([
            'pelaksanaan_sidang_id' => $pelaksanaan->id,
            'dosen_id' => $request->penguji_1_id,
            'role' => 'penguji_1',
        ]);

        PengujiSidang::create([
            'pelaksanaan_sidang_id' => $pelaksanaan->id,
            'dosen_id' => $request->penguji_2_id,
            'role' => 'penguji_2',
        ]);

        return redirect()->route('koordinator.penjadwalan.show', $pendaftaran->jadwal_sidang_id)
            ->with('success', 'Pelaksanaan sidang berhasil dijadwalkan.');
    }

    public function completePelaksanaan(PelaksanaanSidang $pelaksanaan)
    {
        $prodiId = $this->getProdiId();

        if (!$prodiId || $pelaksanaan->pendaftaranSidang->jadwalSidang->prodi_id !== $prodiId) {
            abort(403);
        }

        $pelaksanaan->update(['status' => 'selesai']);

        return back()->with('success', 'Sidang berhasil ditandai selesai.');
    }
}
