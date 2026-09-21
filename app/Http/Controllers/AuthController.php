<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Menampilkan halaman login
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Memproses login manual
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return $this->redirectBasedOnRole(Auth::user());
        }

        return back()->withErrors([
            'email' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
        ])->onlyInput('email');
    }

    /**
     * Fitur kemudahan pengembangan: Login cepat 1-klik untuk akun demo
     */
    public function quickLogin(string $role)
    {
        $user = match ($role) {
            'mahasiswa' => User::where('role', 'mahasiswa')->first(),
            'dosen1'    => User::where('email', 'dosen1@example.com')->first(),
            'dosen2'    => User::where('email', 'dosen2@example.com')->first(),
            'admin'     => User::where('role', 'admin')->first(),
            default     => null,
        };

        if ($user) {
            Auth::login($user);
            request()->session()->regenerate();

            return $this->redirectBasedOnRole($user);
        }

        return redirect()->route('login')->with('error', 'User demo belum di-seed ke database.');
    }

    /**
     * Memproses logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }

    /**
     * Helper redirect berdasarkan role
     */
    private function redirectBasedOnRole(User $user)
    {
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isMahasiswa()) {
            return redirect()->route('mahasiswa.dashboard');
        }

        return redirect()->route('dosen.proposals.available');
    }
}
