<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Periode;
use App\Models\TopikSkripsi;
use App\Models\Unit;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_mahasiswa' => Mahasiswa::count(),
            'total_dosen' => Dosen::count(),
            'total_prodi' => Unit::prodi()->count(),
            'topik_menunggu' => TopikSkripsi::pending()->count(),
            'topik_diterima' => TopikSkripsi::approved()->count(),
            'periode_aktif' => Periode::active()->first(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
