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
        $koordinator = $dosen?->activeKoordinatorProdi;
        return $koordinator?->prodi_id;
    }

    public function index(Request $request)
    {
        $prodiId = $this->getProdiId();

        if (!$prodiId) {
            return redirect()->route('dosen.dashboard')
                ->with('error', 'Anda tidak memiliki akses sebagai koordinator.');
        }

        $jenis = $request->get('jenis', 'sempro');
        $jenisDb = $jenis === 'sempro' ? 'seminar_proposal' : 'sidang_skripsi';

        $jadwals = JadwalSidang::where('prodi_id', $prodiId)
            ->where('jenis', $jenisDb)
            ->with('periode')
            ->withCount('pendaftaranSidang')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('koordinator.penjadwalan.index', compact('jadwals', 'jenis'));
    }

    public function create(Request $request)
    {
        $prodiId = $this->getProdiId();

        if (!$prodiId) {
            return redirect()->route('dosen.dashboard')
                ->with('error', 'Anda tidak memiliki akses sebagai koordinator.');
        }

        $jenis = $request->get('jenis', 'sempro');
        $periodes = Periode::orderBy('tahun_akademik', 'desc')->get();

        return view('koordinator.penjadwalan.create', compact('periodes', 'jenis'));
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
            'nama' => 'required|max:150',
            'tanggal_buka' => 'required|date',
            'tanggal_tutup' => 'required|date|after:tanggal_buka',
            'is_active' => 'boolean',
        ]);

        JadwalSidang::create([
            'prodi_id' => $prodiId,
            'periode_id' => $request->periode_id,
            'jenis' => $request->jenis,
            'nama' => $request->nama,
            'tanggal_buka' => $request->tanggal_buka,
            'tanggal_tutup' => $request->tanggal_tutup,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $jenisParam = $request->jenis === 'seminar_proposal' ? 'sempro' : 'sidang';

        return redirect()->route('koordinator.penjadwalan.index', ['jenis' => $jenisParam])
            ->with('success', 'Jadwal sidang berhasil dibuat.');
    }

    public function show(JadwalSidang $jadwal)
    {
        $prodiId = $this->getProdiId();

        if (!$prodiId || $jadwal->prodi_id !== $prodiId) {
            abort(403);
        }

        $jadwal->load('periode');

        $pendaftarans = PendaftaranSidang::where('jadwal_sidang_id', $jadwal->id)
            ->with(['topik.mahasiswa.user', 'topik.usulanPembimbing.dosen', 'pelaksanaanSidang'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $dosens = Dosen::where('prodi_id', $prodiId)->with('user')->get();

        return view('koordinator.penjadwalan.show', compact('jadwal', 'pendaftarans', 'dosens'));
    }

    public function edit(JadwalSidang $jadwal)
    {
        $prodiId = $this->getProdiId();

        if (!$prodiId || $jadwal->prodi_id !== $prodiId) {
            abort(403);
        }

        $periodes = Periode::orderBy('tahun_akademik', 'desc')->get();

        return view('koordinator.penjadwalan.edit', compact('jadwal', 'periodes'));
    }

    public function update(Request $request, JadwalSidang $jadwal)
    {
        $prodiId = $this->getProdiId();

        if (!$prodiId || $jadwal->prodi_id !== $prodiId) {
            abort(403);
        }

        $request->validate([
            'periode_id' => 'required|exists:periode,id',
            'nama' => 'required|max:150',
            'tanggal_buka' => 'required|date',
            'tanggal_tutup' => 'required|date|after:tanggal_buka',
            'is_active' => 'boolean',
        ]);

        $jadwal->update([
            'periode_id' => $request->periode_id,
            'nama' => $request->nama,
            'tanggal_buka' => $request->tanggal_buka,
            'tanggal_tutup' => $request->tanggal_tutup,
            'is_active' => $request->boolean('is_active'),
        ]);

        $jenisParam = $jadwal->jenis === 'seminar_proposal' ? 'sempro' : 'sidang';

        return redirect()->route('koordinator.penjadwalan.index', ['jenis' => $jenisParam])
            ->with('success', 'Jadwal sidang berhasil diperbarui.');
    }

    public function destroy(JadwalSidang $jadwal)
    {
        $prodiId = $this->getProdiId();

        if (!$prodiId || $jadwal->prodi_id !== $prodiId) {
            abort(403);
        }

        if ($jadwal->pendaftaranSidang()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus jadwal yang sudah memiliki pendaftaran.');
        }

        $jenisParam = $jadwal->jenis === 'seminar_proposal' ? 'sempro' : 'sidang';
        
        $jadwal->delete();

        return redirect()->route('koordinator.penjadwalan.index', ['jenis' => $jenisParam])
            ->with('success', 'Jadwal sidang berhasil dihapus.');
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

        if ($pendaftaran->pelaksanaanSidang) {
            return back()->with('error', 'Sidang sudah dijadwalkan sebelumnya.');
        }

        $dosens = Dosen::where('prodi_id', $prodiId)->with('user')->get();

        return view('koordinator.penjadwalan.create-pelaksanaan', compact('pendaftaran', 'dosens'));
    }

    public function storePelaksanaan(Request $request, PendaftaranSidang $pendaftaran)
    {
        $prodiId = $this->getProdiId();

        if (!$prodiId || $pendaftaran->jadwalSidang->prodi_id !== $prodiId) {
            abort(403);
        }

        if ($pendaftaran->pelaksanaanSidang) {
            return back()->with('error', 'Sidang sudah dijadwalkan sebelumnya.');
        }

        $request->validate([
            'tanggal_sidang' => 'required|date',
            'tempat' => 'required|max:100',
            'penguji_1_id' => 'required|exists:dosen,id',
            'penguji_2_id' => 'required|exists:dosen,id|different:penguji_1_id',
        ]);

        // Cek bentrokan jadwal (tanggal & tempat yang sama)
        $existingSchedule = PelaksanaanSidang::where('tanggal_sidang', $request->tanggal_sidang)
            ->where('tempat', $request->tempat)
            ->where('status', '!=', 'selesai')
            ->first();

        if ($existingSchedule) {
            return back()->withInput()->with('error', 'Jadwal bentrok! Ruangan "' . $request->tempat . '" sudah digunakan pada tanggal tersebut untuk sidang lain.');
        }

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
