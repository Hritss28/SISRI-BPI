<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use Illuminate\Http\Request;

class BimbinganController extends Controller
{
    public function index(Request $request)
    {
        $dosen = auth()->user()->dosen;
        $jenis = $request->get('jenis', 'proposal');

        $bimbingans = Bimbingan::where('dosen_id', $dosen->id)
            ->where('jenis', $jenis)
            ->with('topik.mahasiswa')
            ->orderByRaw("CASE WHEN status = 'menunggu' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('dosen.bimbingan.index', compact('bimbingans', 'jenis'));
    }

    public function show(Bimbingan $bimbingan)
    {
        $dosen = auth()->user()->dosen;

        if ($bimbingan->dosen_id !== $dosen->id) {
            abort(403);
        }

        $bimbingan->load('topik.mahasiswa');

        return view('dosen.bimbingan.show', compact('bimbingan'));
    }

    public function respond(Request $request, Bimbingan $bimbingan)
    {
        $dosen = auth()->user()->dosen;

        if ($bimbingan->dosen_id !== $dosen->id) {
            abort(403);
        }

        if ($bimbingan->status !== 'menunggu') {
            return back()->with('error', 'Bimbingan sudah divalidasi sebelumnya.');
        }

        $request->validate([
            'status' => 'required|in:direvisi,disetujui',
            'pesan_dosen' => 'required',
        ]);

        $bimbingan->update([
            'status' => $request->status,
            'pesan_dosen' => $request->pesan_dosen,
            'tanggal_respon' => now(),
        ]);

        $message = $request->status === 'disetujui' 
            ? 'Bimbingan berhasil disetujui.' 
            : 'Bimbingan perlu direvisi.';

        return redirect()->route('dosen.bimbingan.index', ['jenis' => $bimbingan->jenis])
            ->with('success', $message);
    }
}
