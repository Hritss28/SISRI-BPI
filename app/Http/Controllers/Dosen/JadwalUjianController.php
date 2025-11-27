<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\PelaksanaanSidang;
use App\Models\PengujiSidang;
use Illuminate\Http\Request;

class JadwalUjianController extends Controller
{
    /**
     * Display jadwal sempro where dosen is penguji or pembimbing.
     */
    public function sempro()
    {
        $dosen = auth()->user()->dosen;

        // Get pelaksanaan sidang where dosen is penguji for sempro
        $jadwalSempro = PelaksanaanSidang::with([
            'pendaftaranSidang.topik.mahasiswa.user',
            'pendaftaranSidang.topik.usulanPembimbing1.dosen.user',
            'pendaftaranSidang.topik.usulanPembimbing2.dosen.user',
            'pendaftaranSidang.jadwalSidang.periode',
            'pengujiSidang.dosen.user',
        ])
        ->whereHas('pendaftaranSidang', function ($query) {
            $query->where('jenis', 'seminar_proposal');
        })
        ->where(function ($query) use ($dosen) {
            // Dosen as penguji
            $query->whereHas('pengujiSidang', function ($q) use ($dosen) {
                $q->where('dosen_id', $dosen->id);
            })
            // Or dosen as pembimbing
            ->orWhereHas('pendaftaranSidang.topik.usulanPembimbing', function ($q) use ($dosen) {
                $q->where('dosen_id', $dosen->id)
                  ->where('status', 'diterima');
            });
        })
        ->orderBy('tanggal_sidang', 'desc')
        ->paginate(15);

        return view('dosen.jadwal-ujian.sempro', compact('jadwalSempro', 'dosen'));
    }

    /**
     * Display jadwal sidang skripsi where dosen is penguji or pembimbing.
     */
    public function sidang()
    {
        $dosen = auth()->user()->dosen;

        // Get pelaksanaan sidang where dosen is penguji for sidang skripsi
        $jadwalSidang = PelaksanaanSidang::with([
            'pendaftaranSidang.topik.mahasiswa.user',
            'pendaftaranSidang.topik.usulanPembimbing1.dosen.user',
            'pendaftaranSidang.topik.usulanPembimbing2.dosen.user',
            'pendaftaranSidang.jadwalSidang.periode',
            'pengujiSidang.dosen.user',
        ])
        ->whereHas('pendaftaranSidang', function ($query) {
            $query->where('jenis', 'sidang_skripsi');
        })
        ->where(function ($query) use ($dosen) {
            // Dosen as penguji
            $query->whereHas('pengujiSidang', function ($q) use ($dosen) {
                $q->where('dosen_id', $dosen->id);
            })
            // Or dosen as pembimbing
            ->orWhereHas('pendaftaranSidang.topik.usulanPembimbing', function ($q) use ($dosen) {
                $q->where('dosen_id', $dosen->id)
                  ->where('status', 'diterima');
            });
        })
        ->orderBy('tanggal_sidang', 'desc')
        ->paginate(15);

        return view('dosen.jadwal-ujian.sidang', compact('jadwalSidang', 'dosen'));
    }

    /**
     * Show detail of a specific pelaksanaan.
     */
    public function show(PelaksanaanSidang $pelaksanaan)
    {
        $dosen = auth()->user()->dosen;

        $pelaksanaan->load([
            'pendaftaranSidang.topik.mahasiswa.user',
            'pendaftaranSidang.topik.usulanPembimbing1.dosen.user',
            'pendaftaranSidang.topik.usulanPembimbing2.dosen.user',
            'pendaftaranSidang.jadwalSidang.periode',
            'pengujiSidang.dosen.user',
        ]);

        // Check if dosen has access
        $isPenguji = $pelaksanaan->pengujiSidang->where('dosen_id', $dosen->id)->isNotEmpty();
        $isPembimbing = $pelaksanaan->pendaftaranSidang->topik->pembimbing1_id === $dosen->id 
                     || $pelaksanaan->pendaftaranSidang->topik->pembimbing2_id === $dosen->id;

        if (!$isPenguji && !$isPembimbing) {
            abort(403, 'Anda tidak memiliki akses ke jadwal ini.');
        }

        // Determine role of dosen in this ujian
        $role = null;
        if ($isPenguji) {
            $penguji = $pelaksanaan->pengujiSidang->where('dosen_id', $dosen->id)->first();
            $role = $penguji->role;
        } elseif ($pelaksanaan->pendaftaranSidang->topik->pembimbing1_id === $dosen->id) {
            $role = 'Pembimbing 1';
        } elseif ($pelaksanaan->pendaftaranSidang->topik->pembimbing2_id === $dosen->id) {
            $role = 'Pembimbing 2';
        }

        return view('dosen.jadwal-ujian.show', compact('pelaksanaan', 'dosen', 'role'));
    }
}
