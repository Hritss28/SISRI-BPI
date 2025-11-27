<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\BidangMinat;
use App\Models\Dosen;
use App\Models\TopikSkripsi;
use App\Models\UsulanPembimbing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TopikController extends Controller
{
    public function index()
    {
        $mahasiswa = auth()->user()->mahasiswa;
        
        if (!$mahasiswa) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Profil mahasiswa belum lengkap. Silakan lengkapi data profil Anda.');
        }
        
        $topik = TopikSkripsi::where('mahasiswa_id', $mahasiswa->id)
            ->with(['bidangMinat', 'usulanPembimbing.dosen'])
            ->first();

        return view('mahasiswa.topik.index', compact('topik'));
    }

    public function create()
    {
        $mahasiswa = auth()->user()->mahasiswa;
        
        if (!$mahasiswa) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Profil mahasiswa belum lengkap. Silakan lengkapi data profil Anda.');
        }
        
        // Cek apakah sudah punya topik
        if ($mahasiswa->topikSkripsi()->exists()) {
            return redirect()->route('mahasiswa.topik.index')
                ->with('error', 'Anda sudah memiliki topik skripsi.');
        }

        $bidangMinats = BidangMinat::where('prodi_id', $mahasiswa->prodi_id)
            ->active()
            ->get();
        $dosens = Dosen::where('prodi_id', $mahasiswa->prodi_id)->get();

        return view('mahasiswa.topik.create', compact('bidangMinats', 'dosens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required',
            'bidang_minat_id' => 'required|exists:bidang_minat,id',
            'file_proposal' => 'nullable|file|mimes:pdf|max:5120',
            'pembimbing_1_id' => 'required|exists:dosen,id',
            'pembimbing_2_id' => 'required|exists:dosen,id|different:pembimbing_1_id',
        ]);

        $mahasiswa = auth()->user()->mahasiswa;
        
        if (!$mahasiswa) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Profil mahasiswa belum lengkap. Silakan lengkapi data profil Anda.');
        }

        // Cek apakah sudah punya topik
        if ($mahasiswa->topikSkripsi()->exists()) {
            return redirect()->route('mahasiswa.topik.index')
                ->with('error', 'Anda sudah memiliki topik skripsi.');
        }

        $filePath = null;
        if ($request->hasFile('file_proposal')) {
            $filePath = $request->file('file_proposal')->store('proposals', 'public');
        }

        $topik = TopikSkripsi::create([
            'mahasiswa_id' => $mahasiswa->id,
            'bidang_minat_id' => $request->bidang_minat_id,
            'judul' => $request->judul,
            'file_proposal' => $filePath,
            'status' => 'menunggu',
        ]);

        // Buat usulan pembimbing
        UsulanPembimbing::create([
            'topik_id' => $topik->id,
            'dosen_id' => $request->pembimbing_1_id,
            'urutan' => 1,
            'status' => 'menunggu',
        ]);

        UsulanPembimbing::create([
            'topik_id' => $topik->id,
            'dosen_id' => $request->pembimbing_2_id,
            'urutan' => 2,
            'status' => 'menunggu',
        ]);

        return redirect()->route('mahasiswa.topik.index')
            ->with('success', 'Topik skripsi berhasil diajukan.');
    }

    public function edit(TopikSkripsi $topik)
    {
        $mahasiswa = auth()->user()->mahasiswa;
        
        if (!$mahasiswa) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Profil mahasiswa belum lengkap. Silakan lengkapi data profil Anda.');
        }

        if ($topik->mahasiswa_id !== $mahasiswa->id) {
            abort(403);
        }

        if ($topik->status !== 'ditolak') {
            return redirect()->route('mahasiswa.topik.index')
                ->with('error', 'Hanya topik yang ditolak yang dapat diedit.');
        }

        $topik->load('usulanPembimbing.dosen');

        $bidangMinats = BidangMinat::where('prodi_id', $mahasiswa->prodi_id)
            ->active()
            ->get();
        $dosens = Dosen::where('prodi_id', $mahasiswa->prodi_id)->get();

        return view('mahasiswa.topik.edit', compact('topik', 'bidangMinats', 'dosens'));
    }

    public function update(Request $request, TopikSkripsi $topik)
    {
        $mahasiswa = auth()->user()->mahasiswa;
        
        if (!$mahasiswa) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Profil mahasiswa belum lengkap. Silakan lengkapi data profil Anda.');
        }

        if ($topik->mahasiswa_id !== $mahasiswa->id) {
            abort(403);
        }

        $request->validate([
            'judul' => 'required',
            'bidang_minat_id' => 'required|exists:bidang_minat,id',
            'file_proposal' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        if ($request->hasFile('file_proposal')) {
            if ($topik->file_proposal) {
                Storage::disk('public')->delete($topik->file_proposal);
            }
            $topik->file_proposal = $request->file('file_proposal')->store('proposals', 'public');
        }

        $topik->update([
            'judul' => $request->judul,
            'bidang_minat_id' => $request->bidang_minat_id,
            'file_proposal' => $topik->file_proposal,
            'status' => 'menunggu',
            'catatan' => null,
        ]);

        // Reset usulan pembimbing status to menunggu
        $topik->usulanPembimbing()->update(['status' => 'menunggu', 'catatan' => null]);

        return redirect()->route('mahasiswa.topik.index')
            ->with('success', 'Topik skripsi berhasil diperbarui.');
    }
}
