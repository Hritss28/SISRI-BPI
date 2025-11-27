<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use App\Models\PendaftaranSidang;
use App\Models\UsulanPembimbing;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $dosen = auth()->user()->dosen;

        $stats = [
            'usulan_menunggu' => UsulanPembimbing::where('dosen_id', $dosen->id)->pending()->count(),
            'usulan_diterima' => UsulanPembimbing::where('dosen_id', $dosen->id)->approved()->count(),
            'bimbingan_menunggu' => Bimbingan::where('dosen_id', $dosen->id)->pending()->count(),
            'total_bimbingan' => Bimbingan::where('dosen_id', $dosen->id)->count(),
        ];

        $recentUsulan = UsulanPembimbing::where('dosen_id', $dosen->id)
            ->pending()
            ->with('topik.mahasiswa')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentBimbingan = Bimbingan::where('dosen_id', $dosen->id)
            ->pending()
            ->with('topik.mahasiswa')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dosen.dashboard', compact('dosen', 'stats', 'recentUsulan', 'recentBimbingan'));
    }
}
