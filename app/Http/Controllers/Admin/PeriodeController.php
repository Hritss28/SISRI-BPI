<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Periode;
use Illuminate\Http\Request;

class PeriodeController extends Controller
{
    public function index()
    {
        $periodes = Periode::orderBy('tahun_akademik', 'desc')
            ->orderBy('jenis', 'desc')
            ->paginate(15);
        return view('admin.periode.index', compact('periodes'));
    }

    public function create()
    {
        return view('admin.periode.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|max:50',
            'jenis' => 'required|in:ganjil,genap',
            'tahun_akademik' => 'required|max:9',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'is_active' => 'boolean',
        ]);

        // Jika periode baru aktif, nonaktifkan periode lain
        if ($request->boolean('is_active')) {
            Periode::where('is_active', true)->update(['is_active' => false]);
        }

        Periode::create([
            'nama' => $request->nama,
            'jenis' => $request->jenis,
            'tahun_akademik' => $request->tahun_akademik,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.periode.index')
            ->with('success', 'Periode berhasil ditambahkan.');
    }

    public function edit(Periode $periode)
    {
        return view('admin.periode.edit', compact('periode'));
    }

    public function update(Request $request, Periode $periode)
    {
        $request->validate([
            'nama' => 'required|max:50',
            'jenis' => 'required|in:ganjil,genap',
            'tahun_akademik' => 'required|max:9',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'is_active' => 'boolean',
        ]);

        // Jika periode ini diaktifkan, nonaktifkan periode lain
        if ($request->boolean('is_active') && !$periode->is_active) {
            Periode::where('is_active', true)->update(['is_active' => false]);
        }

        $periode->update([
            'nama' => $request->nama,
            'jenis' => $request->jenis,
            'tahun_akademik' => $request->tahun_akademik,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.periode.index')
            ->with('success', 'Periode berhasil diperbarui.');
    }

    public function destroy(Periode $periode)
    {
        $periode->delete();
        
        return redirect()->route('admin.periode.index')
            ->with('success', 'Periode berhasil dihapus.');
    }

    public function activate(Periode $periode)
    {
        Periode::where('is_active', true)->update(['is_active' => false]);
        $periode->update(['is_active' => true]);

        return redirect()->route('admin.periode.index')
            ->with('success', 'Periode berhasil diaktifkan.');
    }
}
