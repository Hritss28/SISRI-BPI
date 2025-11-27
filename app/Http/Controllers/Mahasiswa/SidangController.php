<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\JadwalSidang;
use App\Models\PendaftaranSidang;
use App\Models\TopikSkripsi;
use Illuminate\Http\Request;

class SidangController extends Controller
{
    public function index()
    {
        $mahasiswa = auth()->user()->mahasiswa;
        
        if (!$mahasiswa) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Profil mahasiswa belum lengkap. Silakan lengkapi data profil Anda.');
        }
        
        $topik = TopikSkripsi::where('mahasiswa_id', $mahasiswa->id)
            ->approved()
            ->first();

        if (!$topik) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Anda belum memiliki topik yang disetujui.');
        }

        $pendaftarans = PendaftaranSidang::where('topik_id', $topik->id)
            ->with(['jadwalSidang', 'pelaksanaanSidang'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('mahasiswa.sidang.index', compact('pendaftarans', 'topik'));
    }

    public function create(Request $request)
    {
        $mahasiswa = auth()->user()->mahasiswa;
        
        if (!$mahasiswa) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Profil mahasiswa belum lengkap. Silakan lengkapi data profil Anda.');
        }
        
        $topik = TopikSkripsi::where('mahasiswa_id', $mahasiswa->id)
            ->approved()
            ->first();

        if (!$topik) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Anda belum memiliki topik yang disetujui.');
        }

        $jenis = $request->get('jenis', 'seminar_proposal');

        // Cek apakah sudah mendaftar untuk jenis sidang ini (yang belum ditolak DAN belum selesai dengan tidak lulus)
        $existingPendaftaran = PendaftaranSidang::where('topik_id', $topik->id)
            ->where('jenis', $jenis)
            ->active() // Hanya cek yang statusnya tidak ditolak
            ->where(function($query) {
                // Exclude pendaftaran yang sudah selesai tapi tidak lulus (boleh daftar ulang)
                $query->whereDoesntHave('pelaksanaanSidang', function($q) {
                    $q->where('status', 'selesai');
                })
                ->orWhereHas('pelaksanaanSidang', function($q) {
                    // Atau yang selesai tapi LULUS (tidak boleh daftar lagi)
                    $q->where('status', 'selesai')
                      ->whereRaw('(SELECT AVG(nilai) FROM nilai WHERE nilai.pelaksanaan_sidang_id = pelaksanaan_sidang.id AND nilai.jenis_nilai = "ujian") >= 55');
                });
            })
            ->exists();

        if ($existingPendaftaran) {
            return redirect()->route('mahasiswa.sidang.index')
                ->with('error', 'Anda sudah mendaftar untuk ' . str_replace('_', ' ', $jenis) . '.');
        }

        // Jika mendaftar sidang skripsi, cek apakah sudah lulus seminar proposal
        if ($jenis === 'sidang_skripsi') {
            $seminarProposal = PendaftaranSidang::where('topik_id', $topik->id)
                ->where('jenis', 'seminar_proposal')
                ->whereHas('pelaksanaanSidang', function($query) {
                    $query->where('status', 'selesai');
                })
                ->with('pelaksanaanSidang.nilai')
                ->first();
            
            if (!$seminarProposal || !$seminarProposal->pelaksanaanSidang) {
                return redirect()->route('mahasiswa.sidang.index')
                    ->with('error', 'Anda belum lulus seminar proposal. Selesaikan seminar proposal terlebih dahulu.');
            }
            
            // Cek apakah nilai lulus (>= C)
            if (!$seminarProposal->pelaksanaanSidang->isLulus()) {
                return redirect()->route('mahasiswa.sidang.index')
                    ->with('error', 'Nilai seminar proposal Anda belum memenuhi syarat (minimal C). Silakan daftar ulang seminar proposal.');
            }
        }

        // Jika mendaftar ulang seminar proposal, cek apakah nilai sebelumnya tidak lulus
        if ($jenis === 'seminar_proposal') {
            $previousSempro = PendaftaranSidang::where('topik_id', $topik->id)
                ->where('jenis', 'seminar_proposal')
                ->whereHas('pelaksanaanSidang', function($query) {
                    $query->where('status', 'selesai');
                })
                ->with('pelaksanaanSidang.nilai')
                ->latest()
                ->first();
            
            // Jika sudah ada nilai dan sudah lulus, tidak bisa daftar lagi
            if ($previousSempro && $previousSempro->pelaksanaanSidang && $previousSempro->pelaksanaanSidang->isLulus()) {
                return redirect()->route('mahasiswa.sidang.index')
                    ->with('error', 'Anda sudah lulus seminar proposal. Silakan lanjutkan ke sidang skripsi.');
            }
        }

        $jadwals = JadwalSidang::where('prodi_id', $mahasiswa->prodi_id)
            ->where('jenis', $jenis)
            ->active()
            ->where('tanggal_buka', '<=', now())
            ->where('tanggal_tutup', '>=', now())
            ->get();

        return view('mahasiswa.sidang.create', compact('topik', 'jenis', 'jadwals'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jadwal_sidang_id' => 'required|exists:jadwal_sidang,id',
            'jenis' => 'required|in:seminar_proposal,sidang_skripsi',
        ]);

        $mahasiswa = auth()->user()->mahasiswa;
        
        if (!$mahasiswa) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Profil mahasiswa belum lengkap. Silakan lengkapi data profil Anda.');
        }
        
        $topik = TopikSkripsi::where('mahasiswa_id', $mahasiswa->id)
            ->approved()
            ->first();

        if (!$topik) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Anda belum memiliki topik yang disetujui.');
        }

        // Cek apakah sudah mendaftar (yang belum ditolak DAN belum selesai dengan tidak lulus)
        $existingPendaftaran = PendaftaranSidang::where('topik_id', $topik->id)
            ->where('jenis', $request->jenis)
            ->active() // Hanya cek yang statusnya tidak ditolak
            ->where(function($query) {
                // Exclude pendaftaran yang sudah selesai tapi tidak lulus (boleh daftar ulang)
                $query->whereDoesntHave('pelaksanaanSidang', function($q) {
                    $q->where('status', 'selesai');
                })
                ->orWhereHas('pelaksanaanSidang', function($q) {
                    // Atau yang selesai tapi LULUS (tidak boleh daftar lagi)
                    $q->where('status', 'selesai')
                      ->whereRaw('(SELECT AVG(nilai) FROM nilai WHERE nilai.pelaksanaan_sidang_id = pelaksanaan_sidang.id AND nilai.jenis_nilai = "ujian") >= 55');
                });
            })
            ->exists();

        if ($existingPendaftaran) {
            return redirect()->route('mahasiswa.sidang.index')
                ->with('error', 'Anda sudah mendaftar untuk ' . str_replace('_', ' ', $request->jenis) . '.');
        }

        // Jika mendaftar sidang skripsi, cek apakah sudah lulus seminar proposal
        if ($request->jenis === 'sidang_skripsi') {
            $seminarProposal = PendaftaranSidang::where('topik_id', $topik->id)
                ->where('jenis', 'seminar_proposal')
                ->whereHas('pelaksanaanSidang', function($query) {
                    $query->where('status', 'selesai');
                })
                ->with('pelaksanaanSidang.nilai')
                ->first();
            
            if (!$seminarProposal || !$seminarProposal->pelaksanaanSidang) {
                return redirect()->route('mahasiswa.sidang.index')
                    ->with('error', 'Anda belum lulus seminar proposal. Selesaikan seminar proposal terlebih dahulu.');
            }
            
            // Cek apakah nilai lulus (>= C)
            if (!$seminarProposal->pelaksanaanSidang->isLulus()) {
                return redirect()->route('mahasiswa.sidang.index')
                    ->with('error', 'Nilai seminar proposal Anda belum memenuhi syarat (minimal C). Silakan daftar ulang seminar proposal.');
            }
        }

        // Jika mendaftar ulang seminar proposal, cek apakah nilai sebelumnya tidak lulus
        if ($request->jenis === 'seminar_proposal') {
            $previousSempro = PendaftaranSidang::where('topik_id', $topik->id)
                ->where('jenis', 'seminar_proposal')
                ->whereHas('pelaksanaanSidang', function($query) {
                    $query->where('status', 'selesai');
                })
                ->with('pelaksanaanSidang.nilai')
                ->latest()
                ->first();
            
            // Jika sudah ada nilai dan sudah lulus, tidak bisa daftar lagi
            if ($previousSempro && $previousSempro->pelaksanaanSidang && $previousSempro->pelaksanaanSidang->isLulus()) {
                return redirect()->route('mahasiswa.sidang.index')
                    ->with('error', 'Anda sudah lulus seminar proposal. Silakan lanjutkan ke sidang skripsi.');
            }
        }

        // Cek jadwal masih buka
        $jadwal = JadwalSidang::find($request->jadwal_sidang_id);
        if (!$jadwal->isOpen()) {
            return back()->with('error', 'Jadwal pendaftaran sudah ditutup.');
        }

        // Cek apakah ada pendaftaran yang tidak lulus sebelumnya untuk di-update
        $failedPendaftaran = PendaftaranSidang::where('topik_id', $topik->id)
            ->where('jenis', $request->jenis)
            ->whereHas('pelaksanaanSidang', function($query) {
                $query->where('status', 'selesai');
            })
            ->with('pelaksanaanSidang')
            ->latest()
            ->first();

        if ($failedPendaftaran && $failedPendaftaran->pelaksanaanSidang && !$failedPendaftaran->pelaksanaanSidang->isLulus()) {
            // Update pendaftaran yang tidak lulus, reset status persetujuan dan hapus pelaksanaan lama
            $failedPendaftaran->pelaksanaanSidang->delete(); // Hapus pelaksanaan sidang lama
            $failedPendaftaran->update([
                'jadwal_sidang_id' => $request->jadwal_sidang_id,
                'status_pembimbing_1' => 'menunggu',
                'status_pembimbing_2' => 'menunggu',
                'status_koordinator' => 'menunggu',
                'catatan_pembimbing_1' => null,
                'catatan_pembimbing_2' => null,
                'catatan_koordinator' => null,
            ]);

            return redirect()->route('mahasiswa.sidang.index')
                ->with('success', 'Pendaftaran ulang ' . str_replace('_', ' ', $request->jenis) . ' berhasil diajukan.');
        }

        // Cek apakah ada pendaftaran yang ditolak sebelumnya untuk di-update
        $rejectedPendaftaran = PendaftaranSidang::where('topik_id', $topik->id)
            ->where('jenis', $request->jenis)
            ->rejected()
            ->first();

        if ($rejectedPendaftaran) {
            // Update pendaftaran yang ditolak, reset status persetujuan
            $rejectedPendaftaran->update([
                'jadwal_sidang_id' => $request->jadwal_sidang_id,
                'status_pembimbing_1' => 'menunggu',
                'status_pembimbing_2' => 'menunggu',
                'status_koordinator' => 'menunggu',
                'catatan_pembimbing_1' => null,
                'catatan_pembimbing_2' => null,
                'catatan_koordinator' => null,
            ]);

            return redirect()->route('mahasiswa.sidang.index')
                ->with('success', 'Pendaftaran sidang berhasil diajukan ulang.');
        }

        // Jika belum pernah mendaftar, buat baru
        PendaftaranSidang::create([
            'topik_id' => $topik->id,
            'jadwal_sidang_id' => $request->jadwal_sidang_id,
            'jenis' => $request->jenis,
        ]);

        return redirect()->route('mahasiswa.sidang.index')
            ->with('success', 'Pendaftaran sidang berhasil diajukan.');
    }

    public function show(PendaftaranSidang $pendaftaran)
    {
        $mahasiswa = auth()->user()->mahasiswa;
        
        if (!$mahasiswa) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Profil mahasiswa belum lengkap. Silakan lengkapi data profil Anda.');
        }
        
        if ($pendaftaran->topik->mahasiswa_id !== $mahasiswa->id) {
            abort(403);
        }

        $pendaftaran->load(['jadwalSidang', 'pelaksanaanSidang.pengujiSidang.dosen', 'pelaksanaanSidang.nilai']);

        return view('mahasiswa.sidang.show', compact('pendaftaran'));
    }
}
