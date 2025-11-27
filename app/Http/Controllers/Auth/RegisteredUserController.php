<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Mahasiswa;
use App\Models\Unit;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:'.User::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Use name if provided, otherwise use username as name
        $name = $request->name ?? $request->username;

        $user = User::create([
            'name' => $name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'mahasiswa', // Default role for registration
            'is_active' => true,
        ]);

        // Assign role using Spatie
        $user->assignRole('mahasiswa');

        // Get default prodi (first prodi in database)
        $defaultProdi = Unit::where('type', 'prodi')->first();

        // Create mahasiswa record
        Mahasiswa::create([
            'user_id' => $user->id,
            'nim' => $request->username,
            'nama' => $name,
            'prodi_id' => $defaultProdi?->id ?? 1,
            'angkatan' => date('Y'),
            'email' => $request->email,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
