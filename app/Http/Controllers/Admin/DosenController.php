<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DosenController extends Controller
{
    public function index()
    {
        $dosen = Dosen::with(['user', 'prodi'])->paginate(15);
        return view('admin.dosen.index', compact('dosen'));
    }

    public function create()
    {
        $prodis = Unit::prodi()->get();
        return view('admin.dosen.create', compact('prodis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nip' => 'nullable|unique:dosen,nip|max:20',
            'nidn' => 'nullable|unique:dosen,nidn|max:20',
            'nama' => 'required|max:100',
            'email' => 'required|email|unique:users,email|max:100',
            'prodi_id' => 'required|exists:units,id',
            'no_hp' => 'nullable|max:15',
            'password' => 'required|min:8|confirmed',
        ]);

        $username = $request->nip ?? $request->nidn ?? strtolower(str_replace(' ', '', $request->nama));

        $user = User::create([
            'username' => $username,
            'name' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'dosen',
            'is_active' => true,
        ]);

        Dosen::create([
            'user_id' => $user->id,
            'nip' => $request->nip,
            'nidn' => $request->nidn,
            'nama' => $request->nama,
            'prodi_id' => $request->prodi_id,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
        ]);

        return redirect()->route('admin.dosen.index')
            ->with('success', 'Dosen berhasil ditambahkan.');
    }

    public function show(Dosen $dosen)
    {
        $dosen->load(['user', 'prodi', 'usulanPembimbing', 'bimbingan']);
        return view('admin.dosen.show', compact('dosen'));
    }

    public function edit(Dosen $dosen)
    {
        $prodis = Unit::prodi()->get();
        return view('admin.dosen.edit', compact('dosen', 'prodis'));
    }

    public function update(Request $request, Dosen $dosen)
    {
        $request->validate([
            'nip' => 'nullable|max:20|unique:dosen,nip,' . $dosen->id,
            'nidn' => 'nullable|max:20|unique:dosen,nidn,' . $dosen->id,
            'nama' => 'required|max:100',
            'email' => 'required|email|max:100|unique:users,email,' . $dosen->user_id,
            'prodi_id' => 'required|exists:units,id',
            'no_hp' => 'nullable|max:15',
            'is_active' => 'boolean',
        ]);

        $dosen->update([
            'nip' => $request->nip,
            'nidn' => $request->nidn,
            'nama' => $request->nama,
            'prodi_id' => $request->prodi_id,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
        ]);

        $dosen->user->update([
            'name' => $request->nama,
            'email' => $request->email,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.dosen.index')
            ->with('success', 'Data dosen berhasil diperbarui.');
    }

    public function destroy(Dosen $dosen)
    {
        $dosen->user->delete();
        
        return redirect()->route('admin.dosen.index')
            ->with('success', 'Dosen berhasil dihapus.');
    }

    /**
     * Reset password dosen ke default
     */
    public function resetPassword(Dosen $dosen)
    {
        $defaultPassword = 'password123';
        
        $dosen->user->update([
            'password' => Hash::make($defaultPassword),
        ]);

        return redirect()->back()
            ->with('success', "Password dosen {$dosen->nama} berhasil direset ke: {$defaultPassword}");
    }
}
