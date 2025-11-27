<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\BidangMinat;
use Illuminate\Http\Request;

class BidangMinatController extends Controller
{
    private function getProdiId()
    {
        $dosen = auth()->user()->dosen;
        $koordinator = $dosen?->activeKoordinatorProdi;
        return $koordinator?->prodi_id;
    }

    public function index()
    {
        $prodiId = $this->getProdiId();

        if (!$prodiId) {
            return redirect()->route('dosen.dashboard')
                ->with('error', 'Anda tidak memiliki akses sebagai koordinator.');
        }

        $bidangMinats = BidangMinat::where('prodi_id', $prodiId)
            ->withCount('topikSkripsi')
            ->paginate(15);

        return view('koordinator.bidang-minat.index', compact('bidangMinats'));
    }

    public function create()
    {
        $prodiId = $this->getProdiId();

        if (!$prodiId) {
            return redirect()->route('dosen.dashboard')
                ->with('error', 'Anda tidak memiliki akses sebagai koordinator.');
        }

        return view('koordinator.bidang-minat.create');
    }

    public function store(Request $request)
    {
        $prodiId = $this->getProdiId();

        if (!$prodiId) {
            return redirect()->route('dosen.dashboard')
                ->with('error', 'Anda tidak memiliki akses sebagai koordinator.');
        }

        $request->validate([
            'nama' => 'required|max:100',
            'deskripsi' => 'nullable',
            'is_active' => 'boolean',
        ]);

        BidangMinat::create([
            'prodi_id' => $prodiId,
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('koordinator.bidang-minat.index')
            ->with('success', 'Bidang minat berhasil ditambahkan.');
    }

    public function edit(BidangMinat $bidangMinat)
    {
        $prodiId = $this->getProdiId();

        if (!$prodiId || $bidangMinat->prodi_id !== $prodiId) {
            abort(403);
        }

        return view('koordinator.bidang-minat.edit', compact('bidangMinat'));
    }

    public function update(Request $request, BidangMinat $bidangMinat)
    {
        $prodiId = $this->getProdiId();

        if (!$prodiId || $bidangMinat->prodi_id !== $prodiId) {
            abort(403);
        }

        $request->validate([
            'nama' => 'required|max:100',
            'deskripsi' => 'nullable',
            'is_active' => 'boolean',
        ]);

        $bidangMinat->update([
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('koordinator.bidang-minat.index')
            ->with('success', 'Bidang minat berhasil diperbarui.');
    }

    public function destroy(BidangMinat $bidangMinat)
    {
        $prodiId = $this->getProdiId();

        if (!$prodiId || $bidangMinat->prodi_id !== $prodiId) {
            abort(403);
        }

        $bidangMinat->delete();

        return redirect()->route('koordinator.bidang-minat.index')
            ->with('success', 'Bidang minat berhasil dihapus.');
    }
}
