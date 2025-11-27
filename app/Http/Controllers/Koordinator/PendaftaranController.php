<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\PelaksanaanSidang;
use App\Models\PendaftaranSidang;
use App\Models\PengujiSidang;
use Illuminate\Http\Request;

class PendaftaranController extends Controller
{
    private function getProdiId()
    {
        $dosen = auth()->user()->dosen;
        $koordinator = $dosen?->activeKoordinatorProdi;
        return $koordinator?->prodi_id;
    }

    /**
     * Menampilkan daftar pendaftaran sempro/sidang yang perlu diproses
     */
    public function index(Request $request)
    {
        $prodiId = $this->getProdiId();

        if (!$prodiId) {
            return redirect()->route('dosen.dashboard')
                ->with('error', 'Anda tidak memiliki akses sebagai koordinator.');
        }

        $jenis = $request->get('jenis', 'sempro');
        $jenisDb = $jenis === 'sempro' ? 'seminar_proposal' : 'sidang_skripsi';

        // Ambil pendaftaran yang sudah disetujui kedua pembimbing
        $pendaftarans = PendaftaranSidang::where('jenis', $jenisDb)
            ->where('status_pembimbing_1', 'disetujui')
            ->where('status_pembimbing_2', 'disetujui')
            ->whereHas('jadwalSidang', function ($q) use ($prodiId) {
                $q->where('prodi_id', $prodiId);
            })
            ->with([
                'topik.mahasiswa.user',
                'topik.usulanPembimbing' => function ($q) {
                    $q->where('status', 'diterima')->orderBy('urutan');
                },
                'topik.usulanPembimbing.dosen.user',
                'jadwalSidang',
                'pelaksanaanSidang'
            ])
            ->orderByRaw("CASE WHEN status_koordinator = 'menunggu' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('koordinator.pendaftaran.index', compact('pendaftarans', 'jenis'));
    }

    /**
     * Detail pendaftaran
     */
    public function show(PendaftaranSidang $pendaftaran)
    {
        $prodiId = $this->getProdiId();

        if (!$prodiId || $pendaftaran->jadwalSidang->prodi_id !== $prodiId) {
            abort(403);
        }

        $pendaftaran->load([
            'topik.mahasiswa.user',
            'topik.usulanPembimbing' => function ($q) {
                $q->where('status', 'diterima')->orderBy('urutan');
            },
            'topik.usulanPembimbing.dosen.user',
            'topik.bimbingan' => function ($q) {
                $q->orderBy('created_at', 'desc')->limit(5);
            },
            'jadwalSidang',
            'pelaksanaanSidang.pengujiSidang.dosen.user'
        ]);

        // Daftar dosen untuk penguji (exclude pembimbing)
        $pembimbingIds = $pendaftaran->topik->usulanPembimbing->pluck('dosen_id')->toArray();
        $dosens = Dosen::where('prodi_id', $prodiId)
            ->whereNotIn('id', $pembimbingIds)
            ->with('user')
            ->get();

        $jenis = $pendaftaran->jenis === 'seminar_proposal' ? 'sempro' : 'sidang';

        return view('koordinator.pendaftaran.show', compact('pendaftaran', 'dosens', 'jenis'));
    }

    /**
     * Setujui pendaftaran dan jadwalkan pelaksanaan
     */
    public function approve(Request $request, PendaftaranSidang $pendaftaran)
    {
        $prodiId = $this->getProdiId();

        if (!$prodiId || $pendaftaran->jadwalSidang->prodi_id !== $prodiId) {
            abort(403);
        }

        $request->validate([
            'tanggal_sidang' => 'required|date|after:today',
            'tempat' => 'required|string|max:255',
            'penguji_1_id' => 'required|exists:dosen,id',
            'penguji_2_id' => 'nullable|exists:dosen,id|different:penguji_1_id',
            'catatan' => 'nullable|string',
        ]);

        // Update status koordinator
        $pendaftaran->update([
            'status_koordinator' => 'disetujui',
            'catatan_koordinator' => $request->catatan,
        ]);

        // Buat pelaksanaan sidang
        $pelaksanaan = PelaksanaanSidang::create([
            'pendaftaran_sidang_id' => $pendaftaran->id,
            'tanggal_sidang' => $request->tanggal_sidang,
            'tempat' => $request->tempat,
            'status' => 'dijadwalkan',
        ]);

        // Tambahkan pembimbing sebagai penguji
        $pembimbing = $pendaftaran->topik->usulanPembimbing()
            ->where('status', 'diterima')
            ->orderBy('urutan')
            ->get();

        foreach ($pembimbing as $p) {
            PengujiSidang::create([
                'pelaksanaan_sidang_id' => $pelaksanaan->id,
                'dosen_id' => $p->dosen_id,
                'role' => 'pembimbing_' . $p->urutan,
            ]);
        }

        // Tambahkan penguji 1
        PengujiSidang::create([
            'pelaksanaan_sidang_id' => $pelaksanaan->id,
            'dosen_id' => $request->penguji_1_id,
            'role' => 'penguji_1',
        ]);

        // Tambahkan penguji 2 jika ada
        if ($request->penguji_2_id) {
            PengujiSidang::create([
                'pelaksanaan_sidang_id' => $pelaksanaan->id,
                'dosen_id' => $request->penguji_2_id,
                'role' => 'penguji_2',
            ]);
        }

        $jenis = $pendaftaran->jenis === 'seminar_proposal' ? 'sempro' : 'sidang';

        return redirect()->route('koordinator.pendaftaran.index', ['jenis' => $jenis])
            ->with('success', 'Pendaftaran disetujui dan sidang berhasil dijadwalkan.');
    }

    /**
     * Tolak pendaftaran
     */
    public function reject(Request $request, PendaftaranSidang $pendaftaran)
    {
        $prodiId = $this->getProdiId();

        if (!$prodiId || $pendaftaran->jadwalSidang->prodi_id !== $prodiId) {
            abort(403);
        }

        $request->validate([
            'catatan' => 'required|string|max:500',
        ]);

        $pendaftaran->update([
            'status_koordinator' => 'ditolak',
            'catatan_koordinator' => $request->catatan,
        ]);

        $jenis = $pendaftaran->jenis === 'seminar_proposal' ? 'sempro' : 'sidang';

        return redirect()->route('koordinator.pendaftaran.index', ['jenis' => $jenis])
            ->with('success', 'Pendaftaran ditolak.');
    }

    /**
     * Edit jadwal pelaksanaan yang sudah ada
     */
    public function editPelaksanaan(PendaftaranSidang $pendaftaran)
    {
        $prodiId = $this->getProdiId();

        if (!$prodiId || $pendaftaran->jadwalSidang->prodi_id !== $prodiId) {
            abort(403);
        }

        if (!$pendaftaran->pelaksanaanSidang) {
            return redirect()->back()->with('error', 'Pelaksanaan sidang belum dijadwalkan.');
        }

        $pendaftaran->load([
            'topik.mahasiswa.user',
            'topik.usulanPembimbing' => function ($q) {
                $q->where('status', 'diterima')->orderBy('urutan');
            },
            'topik.usulanPembimbing.dosen.user',
            'pelaksanaanSidang.pengujiSidang.dosen.user'
        ]);

        // Daftar dosen untuk penguji (exclude pembimbing)
        $pembimbingIds = $pendaftaran->topik->usulanPembimbing->pluck('dosen_id')->toArray();
        $dosens = Dosen::where('prodi_id', $prodiId)
            ->whereNotIn('id', $pembimbingIds)
            ->with('user')
            ->get();

        $jenis = $pendaftaran->jenis === 'seminar_proposal' ? 'sempro' : 'sidang';

        return view('koordinator.pendaftaran.edit-pelaksanaan', compact('pendaftaran', 'dosens', 'jenis'));
    }

    /**
     * Update jadwal pelaksanaan
     */
    public function updatePelaksanaan(Request $request, PendaftaranSidang $pendaftaran)
    {
        $prodiId = $this->getProdiId();

        if (!$prodiId || $pendaftaran->jadwalSidang->prodi_id !== $prodiId) {
            abort(403);
        }

        $pelaksanaan = $pendaftaran->pelaksanaanSidang;

        if (!$pelaksanaan || $pelaksanaan->status === 'selesai') {
            return redirect()->back()->with('error', 'Tidak dapat mengubah jadwal yang sudah selesai.');
        }

        $request->validate([
            'tanggal_sidang' => 'required|date',
            'tempat' => 'required|string|max:255',
            'penguji_1_id' => 'required|exists:dosen,id',
            'penguji_2_id' => 'nullable|exists:dosen,id|different:penguji_1_id',
        ]);

        // Update pelaksanaan
        $pelaksanaan->update([
            'tanggal_sidang' => $request->tanggal_sidang,
            'tempat' => $request->tempat,
        ]);

        // Update penguji (hapus penguji lama, tambah baru)
        $pelaksanaan->pengujiSidang()->whereIn('role', ['penguji_1', 'penguji_2'])->delete();

        PengujiSidang::create([
            'pelaksanaan_sidang_id' => $pelaksanaan->id,
            'dosen_id' => $request->penguji_1_id,
            'role' => 'penguji_1',
        ]);

        if ($request->penguji_2_id) {
            PengujiSidang::create([
                'pelaksanaan_sidang_id' => $pelaksanaan->id,
                'dosen_id' => $request->penguji_2_id,
                'role' => 'penguji_2',
            ]);
        }

        $jenis = $pendaftaran->jenis === 'seminar_proposal' ? 'sempro' : 'sidang';

        return redirect()->route('koordinator.pendaftaran.index', ['jenis' => $jenis])
            ->with('success', 'Jadwal pelaksanaan berhasil diperbarui.');
    }

    /**
     * Tandai sidang selesai
     */
    public function completePelaksanaan(PelaksanaanSidang $pelaksanaan)
    {
        $prodiId = $this->getProdiId();

        if (!$prodiId || $pelaksanaan->pendaftaranSidang->jadwalSidang->prodi_id !== $prodiId) {
            abort(403);
        }

        $pelaksanaan->update([
            'status' => 'selesai',
        ]);

        $jenis = $pelaksanaan->pendaftaranSidang->jenis === 'seminar_proposal' ? 'sempro' : 'sidang';

        return redirect()->route('koordinator.pendaftaran.index', ['jenis' => $jenis])
            ->with('success', 'Sidang telah ditandai selesai.');
    }
}
