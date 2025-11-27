<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\KoordinatorProdi;
use App\Models\Unit;
use Illuminate\Http\Request;

class KoordinatorProdiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $koordinators = KoordinatorProdi::with(['dosen.user', 'prodi'])
            ->orderBy('is_active', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.koordinator.index', compact('koordinators'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $prodis = Unit::prodi()->get();
        $dosens = Dosen::with('user')
            ->whereHas('user', function ($query) {
                $query->where('is_active', true);
            })
            ->get();

        return view('admin.koordinator.create', compact('prodis', 'dosens'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'prodi_id' => 'required|exists:units,id',
            'dosen_id' => 'required|exists:dosen,id',
            'tahun_mulai' => 'required|integer|min:2000|max:' . (date('Y') + 5),
        ]);

        // Check if prodi already has an active koordinator
        $existingKoordinator = KoordinatorProdi::where('prodi_id', $request->prodi_id)
            ->where('is_active', true)
            ->first();

        if ($existingKoordinator) {
            // Deactivate the existing koordinator and set tahun_selesai
            $existingKoordinator->update([
                'is_active' => false,
                'tahun_selesai' => date('Y'),
            ]);
        }

        // Check if dosen is already koordinator in another prodi
        $dosenKoordinator = KoordinatorProdi::where('dosen_id', $request->dosen_id)
            ->where('is_active', true)
            ->first();

        if ($dosenKoordinator) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Dosen ini sudah menjadi koordinator aktif di prodi lain.');
        }

        // Create new koordinator
        KoordinatorProdi::create([
            'prodi_id' => $request->prodi_id,
            'dosen_id' => $request->dosen_id,
            'tahun_mulai' => $request->tahun_mulai,
            'is_active' => true,
        ]);

        // Update user role to koordinator
        $dosen = Dosen::find($request->dosen_id);
        $dosen->user->update(['role' => 'koordinator']);

        return redirect()->route('admin.koordinator.index')
            ->with('success', 'Koordinator Prodi berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(KoordinatorProdi $koordinator)
    {
        $koordinator->load(['dosen.user', 'prodi']);
        return view('admin.koordinator.show', compact('koordinator'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KoordinatorProdi $koordinator)
    {
        // Simpan dosen_id sebelum dihapus
        $dosenId = $koordinator->dosen_id;

        // Hapus data koordinator dari database
        $koordinator->delete();

        // Check if dosen has other koordinator roles (active or not)
        $otherKoordinator = KoordinatorProdi::where('dosen_id', $dosenId)
            ->where('is_active', true)
            ->exists();

        // If no other active koordinator role, change role back to dosen
        if (!$otherKoordinator) {
            $dosen = Dosen::find($dosenId);
            if ($dosen && $dosen->user) {
                $dosen->user->update(['role' => 'dosen']);
            }
        }

        return redirect()->route('admin.koordinator.index')
            ->with('success', 'Koordinator Prodi berhasil dihapus.');
    }

    /**
     * Toggle active status
     */
    public function toggleStatus(KoordinatorProdi $koordinator)
    {
        if ($koordinator->is_active) {
            // Deactivate
            $koordinator->update(['is_active' => false]);

            // Check if dosen has other active koordinator roles
            $otherKoordinator = KoordinatorProdi::where('dosen_id', $koordinator->dosen_id)
                ->where('id', '!=', $koordinator->id)
                ->where('is_active', true)
                ->exists();

            if (!$otherKoordinator) {
                $koordinator->dosen->user->update(['role' => 'dosen']);
            }

            $message = 'Koordinator Prodi berhasil dinonaktifkan.';
        } else {
            // Check if prodi already has an active koordinator
            $existingKoordinator = KoordinatorProdi::where('prodi_id', $koordinator->prodi_id)
                ->where('is_active', true)
                ->first();

            if ($existingKoordinator) {
                $existingKoordinator->update(['is_active' => false]);
                
                // Check if previous koordinator has other roles
                $otherKoordinator = KoordinatorProdi::where('dosen_id', $existingKoordinator->dosen_id)
                    ->where('id', '!=', $existingKoordinator->id)
                    ->where('is_active', true)
                    ->exists();

                if (!$otherKoordinator) {
                    $existingKoordinator->dosen->user->update(['role' => 'dosen']);
                }
            }

            // Activate
            $koordinator->update(['is_active' => true]);
            $koordinator->dosen->user->update(['role' => 'koordinator']);

            $message = 'Koordinator Prodi berhasil diaktifkan.';
        }

        return redirect()->route('admin.koordinator.index')->with('success', $message);
    }
}
