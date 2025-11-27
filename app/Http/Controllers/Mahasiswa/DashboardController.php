<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use App\Models\PendaftaranSidang;
use App\Models\TopikSkripsi;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $mahasiswa = auth()->user()->mahasiswa;
        
        // Handle jika data mahasiswa belum ada
        if (!$mahasiswa) {
            return view('mahasiswa.dashboard', [
                'mahasiswa' => null,
                'topik' => null,
                'stats' => [
                    'bimbingan_proposal' => 0,
                    'bimbingan_skripsi' => 0,
                    'bimbingan_menunggu' => 0,
                    'pendaftaran_sidang' => 0,
                ],
                'recentBimbingan' => collect(),
                'needsProfile' => true,
            ]);
        }

        $topik = TopikSkripsi::where('mahasiswa_id', $mahasiswa->id)
            ->with(['bidangMinat', 'usulanPembimbing.dosen'])
            ->first();

        $stats = [
            'bimbingan_proposal' => $topik ? Bimbingan::where('topik_id', $topik->id)->proposal()->count() : 0,
            'bimbingan_skripsi' => $topik ? Bimbingan::where('topik_id', $topik->id)->skripsi()->count() : 0,
            'bimbingan_menunggu' => $topik ? Bimbingan::where('topik_id', $topik->id)->pending()->count() : 0,
            'pendaftaran_sidang' => $topik ? PendaftaranSidang::where('topik_id', $topik->id)->count() : 0,
        ];

        $recentBimbingan = $topik 
            ? Bimbingan::where('topik_id', $topik->id)
                ->with('dosen')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
            : collect();

        return view('mahasiswa.dashboard', compact('mahasiswa', 'topik', 'stats', 'recentBimbingan'));
    }
}
