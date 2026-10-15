<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminAdministratorController extends Controller
{
    /**
     * Menampilkan daftar administrator yang terdaftar di sistem
     */
    public function index(Request $request): View
    {
        $query = User::where('role', 'admin');

        // Pencarian Berdasarkan Nama, NIP/ID, Email, atau Jabatan/Unit
        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('nip_nim', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('kepakaran', 'LIKE', "%{$search}%");
            });
        }

        $administrators = $query->latest()->paginate(15)->withQueryString();
        $totalAdmin = User::where('role', 'admin')->count();

        return view('admin.administrators.index', compact('administrators', 'totalAdmin'));
    }

    /**
     * Menampilkan formulir pendaftaran Administrator baru (Setting -> Add Admin)
     */
    public function create(): View
    {
        return view('admin.administrators.create');
    }

    /**
     * Menyimpan data pendaftaran Administrator baru ke sistem
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nip_nim' => ['required', 'string', 'max:50', 'unique:users,nip_nim'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'kepakaran' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'Nama lengkap administrator wajib diisi.',
            'nip_nim.required' => 'NIP / ID Administrator wajib diisi.',
            'nip_nim.unique' => 'NIP / ID tersebut sudah terdaftar di sistem.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Alamat email tersebut sudah digunakan akun lain.',
            'password.required' => 'Password wajib ditentukan.',
            'password.min' => 'Password minimal terdiri dari 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'nip_nim' => $validated['nip_nim'],
            'email' => $validated['email'],
            'kepakaran' => $validated['kepakaran'] ?? 'Administrator Kampus',
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
        ]);

        return redirect()->route('admin.administrators.index')
            ->with('success', "Administrator {$user->name} (NIP/ID: {$user->nip_nim}) berhasil didaftarkan dan berhak mengelola sistem.");
    }

    /**
     * Menampilkan formulir edit akun administrator
     */
    public function edit(User $user): View
    {
        if (! $user->isAdmin()) {
            abort(404, 'Pengguna bukan administrator.');
        }

        return view('admin.administrators.edit', compact('user'));
    }

    /**
     * Memperbarui data akun administrator
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        if (! $user->isAdmin()) {
            abort(404, 'Pengguna bukan administrator.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nip_nim' => ['required', 'string', 'max:50', Rule::unique('users', 'nip_nim')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'kepakaran' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'Nama lengkap administrator wajib diisi.',
            'nip_nim.required' => 'NIP / ID Administrator wajib diisi.',
            'nip_nim.unique' => 'NIP / ID tersebut sudah digunakan akun lain.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Alamat email tersebut sudah digunakan akun lain.',
            'password.min' => 'Password baru minimal terdiri dari 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user->name = $validated['name'];
        $user->nip_nim = $validated['nip_nim'];
        $user->email = $validated['email'];
        $user->kepakaran = $validated['kepakaran'] ?? $user->kepakaran;

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.administrators.index')
            ->with('success', "Data administrator {$user->name} berhasil diperbarui.");
    }

    /**
     * Menghapus akun administrator
     */
    public function destroy(User $user): RedirectResponse
    {
        if (! $user->isAdmin()) {
            abort(404, 'Pengguna bukan administrator.');
        }

        // Proteksi 1: Tidak dapat menghapus akun sendiri yang sedang login
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Aksi ditolak: Anda tidak dapat menghapus akun administrator Anda sendiri yang sedang aktif.');
        }

        // Proteksi 2: Tidak dapat menghapus jika merupakan satu-satunya admin tersisa
        if (User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'Aksi ditolak: Tidak dapat menghapus administrator ini karena merupakan satu-satunya akun administrator yang tersisa di sistem.');
        }

        $name = $user->name;
        $nip = $user->nip_nim;
        $user->delete();

        return redirect()->route('admin.administrators.index')
            ->with('success', "Akun administrator {$name} (NIP/ID: {$nip}) berhasil dihapus dari sistem.");
    }
}
