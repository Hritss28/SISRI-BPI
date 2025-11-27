<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MahasiswaController extends Controller
{
    public function index()
    {
        $mahasiswa = Mahasiswa::with(['user', 'prodi'])->paginate(15);
        return view('admin.mahasiswa.index', compact('mahasiswa'));
    }

    public function create()
    {
        $prodis = Unit::prodi()->get();
        return view('admin.mahasiswa.create', compact('prodis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nim' => 'required|unique:mahasiswa,nim|max:20',
            'nama' => 'required|max:100',
            'email' => 'required|email|unique:users,email|max:100',
            'prodi_id' => 'required|exists:units,id',
            'angkatan' => 'required|max:4',
            'no_hp' => 'nullable|max:15',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'username' => $request->nim,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'mahasiswa',
            'is_active' => true,
        ]);

        Mahasiswa::create([
            'user_id' => $user->id,
            'nim' => $request->nim,
            'nama' => $request->nama,
            'prodi_id' => $request->prodi_id,
            'angkatan' => $request->angkatan,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
        ]);

        return redirect()->route('admin.mahasiswa.index')
            ->with('success', 'Mahasiswa berhasil ditambahkan.');
    }

    public function show(Mahasiswa $mahasiswa)
    {
        $mahasiswa->load(['user', 'prodi', 'topikSkripsi']);
        return view('admin.mahasiswa.show', compact('mahasiswa'));
    }

    public function edit(Mahasiswa $mahasiswa)
    {
        $prodis = Unit::prodi()->get();
        return view('admin.mahasiswa.edit', compact('mahasiswa', 'prodis'));
    }

    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        $request->validate([
            'nim' => 'required|max:20|unique:mahasiswa,nim,' . $mahasiswa->id,
            'nama' => 'required|max:100',
            'email' => 'required|email|max:100|unique:users,email,' . $mahasiswa->user_id,
            'prodi_id' => 'required|exists:units,id',
            'angkatan' => 'required|max:4',
            'no_hp' => 'nullable|max:15',
            'is_active' => 'boolean',
        ]);

        $mahasiswa->update([
            'nim' => $request->nim,
            'nama' => $request->nama,
            'prodi_id' => $request->prodi_id,
            'angkatan' => $request->angkatan,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
        ]);

        $mahasiswa->user->update([
            'username' => $request->nim,
            'email' => $request->email,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.mahasiswa.index')
            ->with('success', 'Data mahasiswa berhasil diperbarui.');
    }

    public function destroy(Mahasiswa $mahasiswa)
    {
        $mahasiswa->user->delete();
        
        return redirect()->route('admin.mahasiswa.index')
            ->with('success', 'Mahasiswa berhasil dihapus.');
    }
}
