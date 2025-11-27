<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\Nilai;
use App\Models\PelaksanaanSidang;
use Illuminate\Http\Request;

class DaftarNilaiController extends Controller
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

        $pelaksanaans = PelaksanaanSidang::whereHas('pendaftaranSidang.jadwalSidang', function ($q) use ($prodiId, $jenisDb) {
            $q->where('prodi_id', $prodiId)
              ->where('jenis', $jenisDb);
        })
        ->with(['pendaftaranSidang.topik.mahasiswa.user', 'pendaftaranSidang.jadwalSidang', 'nilai.dosen'])
        ->completed()
        ->orderBy('tanggal_sidang', 'desc')
        ->paginate(15);

        return view('koordinator.daftar-nilai.index', compact('pelaksanaans', 'jenis'));
    }

    public function show(PelaksanaanSidang $pelaksanaan)
    {
        $prodiId = $this->getProdiId();

        if (!$prodiId || $pelaksanaan->pendaftaranSidang->jadwalSidang->prodi_id !== $prodiId) {
            abort(403);
        }

        $pelaksanaan->load([
            'pendaftaranSidang.topik.mahasiswa',
            'pengujiSidang.dosen',
            'nilai.dosen',
        ]);

        // Hitung rata-rata nilai
        $nilaiRata = $pelaksanaan->nilai->avg('nilai');

        return view('koordinator.daftar-nilai.show', compact('pelaksanaan', 'nilaiRata'));
    }
}
