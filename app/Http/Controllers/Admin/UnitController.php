<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $query = Unit::with('parent')->orderBy('type')->orderBy('nama');
        
        // Filter by type if specified
        if ($request->has('type') && in_array($request->type, ['fakultas', 'jurusan', 'prodi'])) {
            $query->where('type', $request->type);
        }
        
        $units = $query->paginate(15)->withQueryString();
        $currentType = $request->type;
        
        return view('admin.unit.index', compact('units', 'currentType'));
    }

    public function create()
    {
        $parents = Unit::whereIn('type', ['fakultas', 'jurusan'])->get();
        return view('admin.unit.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|unique:units,kode|max:20',
            'nama' => 'required|max:100',
            'type' => 'required|in:fakultas,jurusan,prodi',
            'parent_id' => 'nullable|exists:units,id',
        ]);

        Unit::create([
            'kode' => $request->kode,
            'nama' => $request->nama,
            'type' => $request->type,
            'parent_id' => $request->parent_id,
        ]);

        return redirect()->route('admin.unit.index')
            ->with('success', 'Unit berhasil ditambahkan.');
    }

    public function edit(Unit $unit)
    {
        $parents = Unit::whereIn('type', ['fakultas', 'jurusan'])
            ->where('id', '!=', $unit->id)
            ->get();
        return view('admin.unit.edit', compact('unit', 'parents'));
    }

    public function update(Request $request, Unit $unit)
    {
        $request->validate([
            'kode' => 'required|max:20|unique:units,kode,' . $unit->id,
            'nama' => 'required|max:100',
            'type' => 'required|in:fakultas,jurusan,prodi',
            'parent_id' => 'nullable|exists:units,id',
        ]);

        $unit->update([
            'kode' => $request->kode,
            'nama' => $request->nama,
            'type' => $request->type,
            'parent_id' => $request->parent_id,
        ]);

        return redirect()->route('admin.unit.index')
            ->with('success', 'Unit berhasil diperbarui.');
    }

    public function destroy(Unit $unit)
    {
        $unit->delete();
        
        return redirect()->route('admin.unit.index')
            ->with('success', 'Unit berhasil dihapus.');
    }
}
