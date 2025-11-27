<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\JadwalSidang;
use App\Models\Mahasiswa;
use App\Models\PendaftaranSidang;
use App\Models\TopikSkripsi;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $dosen = auth()->user()->dosen;
        $koordinator = $dosen?->activeKoordinatorProdi;

        if (!$koordinator) {
            return redirect()->route('dosen.dashboard')
                ->with('error', 'Anda tidak memiliki akses sebagai koordinator.');
        }

        $prodiId = $koordinator->prodi_id;

        $stats = [
            'total_mahasiswa' => Mahasiswa::where('prodi_id', $prodiId)->count(),
            'topik_menunggu' => TopikSkripsi::whereHas('mahasiswa', function ($q) use ($prodiId) {
                $q->where('prodi_id', $prodiId);
            })->pending()->count(),
            'topik_diterima' => TopikSkripsi::whereHas('mahasiswa', function ($q) use ($prodiId) {
                $q->where('prodi_id', $prodiId);
            })->approved()->count(),
            'pendaftaran_menunggu' => PendaftaranSidang::where('status_koordinator', 'menunggu')
                ->whereHas('jadwalSidang', function ($q) use ($prodiId) {
                    $q->where('prodi_id', $prodiId);
                })->count(),
            'jadwal_aktif' => JadwalSidang::where('prodi_id', $prodiId)->active()->count(),
        ];

        return view('koordinator.dashboard', compact('koordinator', 'stats'));
    }
}
