<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * REGISTER
     */
    public function store(Request $request)
    {
        // validate input (will redirect back with errors on failure)
        $validated = $request->validate([
            'nip'           => 'required|string|max:255|unique:users,nip',
            'username'      => 'required|string|max:50|unique:users,username',
            'password'      => 'required|string|min:6',
            'role'          => 'required|in:admin,pegawai',
            'penempatan_id' => 'required|exists:penempatan,id',
        ]);


        User::create($validated);

        return redirect()->route('login');
    }

public function login(Request $request)
{
    $credentials = $request->validate([
        'username' => 'required|string',
        'password' => 'required|string',
    ]);

    if (!Auth::attempt($credentials)) {
            return back()->withErrors([
                'username' => 'Username atau password salah',
            ])->withInput($request->only('username'));
    }

    $request->session()->regenerate();

    // 🔐 CEK is_deleted SETELAH LOGIN
    if (Auth::user()->is_deleted !== null) {
        Auth::logout();
        return redirect('/login')->withErrors([
            'username' => 'Akun sudah tidak aktif',
        ]);
    }

    // Redirect based on role
    $user = Auth::user();
    if ($user->role === 'admin') {
        return redirect()->intended(route('admin.dashboard'));
    }
    if ($user->role === 'penempatan') {
        return redirect()->intended(route('admin.pemakaian.daftar'));
    }

    // default pegawai
    return redirect()->intended(route('mobil.pegawai.index'));
}


    /**
     * LOGOUT
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
