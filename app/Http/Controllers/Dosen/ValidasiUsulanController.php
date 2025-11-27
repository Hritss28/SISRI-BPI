<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\UsulanPembimbing;
use Illuminate\Http\Request;

class ValidasiUsulanController extends Controller
{
    public function index()
    {
        $dosen = auth()->user()->dosen;

        $usulans = UsulanPembimbing::where('dosen_id', $dosen->id)
            ->with(['topik.mahasiswa', 'topik.bidangMinat'])
            ->orderByRaw("CASE WHEN status = 'menunggu' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('dosen.validasi-usulan.index', compact('usulans'));
    }

    public function show(UsulanPembimbing $usulan)
    {
        $dosen = auth()->user()->dosen;

        if ($usulan->dosen_id !== $dosen->id) {
            abort(403);
        }

        $usulan->load(['topik.mahasiswa', 'topik.bidangMinat']);

        return view('dosen.validasi-usulan.show', compact('usulan'));
    }

    public function approve(Request $request, UsulanPembimbing $usulan)
    {
        $dosen = auth()->user()->dosen;

        if ($usulan->dosen_id !== $dosen->id) {
            abort(403);
        }

        if ($usulan->status !== 'menunggu') {
            return back()->with('error', 'Usulan sudah divalidasi sebelumnya.');
        }

        $request->validate([
            'jangka_waktu' => 'nullable|date|after:today',
            'catatan' => 'nullable',
        ]);

        $usulan->update([
            'status' => 'diterima',
            'jangka_waktu' => $request->jangka_waktu,
            'catatan' => $request->catatan,
            'tanggal_respon' => now(),
        ]);

        // Cek apakah semua pembimbing sudah menyetujui
        $this->checkAndUpdateTopikStatus($usulan->topik_id);

        return redirect()->route('dosen.validasi-usulan.index')
            ->with('success', 'Usulan pembimbingan berhasil disetujui.');
    }

    public function reject(Request $request, UsulanPembimbing $usulan)
    {
        $dosen = auth()->user()->dosen;

        if ($usulan->dosen_id !== $dosen->id) {
            abort(403);
        }

        if ($usulan->status !== 'menunggu') {
            return back()->with('error', 'Usulan sudah divalidasi sebelumnya.');
        }

        $request->validate([
            'catatan' => 'required',
        ]);

        $usulan->update([
            'status' => 'ditolak',
            'catatan' => $request->catatan,
            'tanggal_respon' => now(),
        ]);

        // Update status topik menjadi ditolak
        $usulan->topik->update([
            'status' => 'ditolak',
            'catatan' => 'Usulan pembimbing ditolak oleh ' . $dosen->nama . ': ' . $request->catatan,
        ]);

        return redirect()->route('dosen.validasi-usulan.index')
            ->with('success', 'Usulan pembimbingan berhasil ditolak.');
    }

    private function checkAndUpdateTopikStatus($topikId)
    {
        $usulans = UsulanPembimbing::where('topik_id', $topikId)->get();
        
        $allApproved = $usulans->every(function ($usulan) {
            return $usulan->status === 'diterima';
        });

        if ($allApproved && $usulans->count() >= 2) {
            $usulans->first()->topik->update([
                'status' => 'diterima',
            ]);
        }
    }
}
