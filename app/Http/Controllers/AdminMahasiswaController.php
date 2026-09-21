<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminMahasiswaController extends Controller
{
    /**
     * Menampilkan daftar mahasiswa yang berhak masuk ke sistem
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'mahasiswa')->withCount('proposalsKetua');

        // Fitur Pencarian Kata Kunci (Nama, NIM, Email)
        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('nip_nim', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        $mahasiswas = $query->latest()->paginate(15)->withQueryString();
        $totalMahasiswa = User::where('role', 'mahasiswa')->count();

        return view('admin.mahasiswa.index', compact('mahasiswas', 'totalMahasiswa'));
    }

    /**
     * Menampilkan form pendaftaran mahasiswa baru (Setting -> Add Mahasiswa)
     */
    public function create()
    {
        return view('admin.mahasiswa.create');
    }

    /**
     * Menyimpan data pendaftaran mahasiswa baru ke sistem
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'nip_nim'  => ['required', 'string', 'max:50', 'unique:users,nip_nim'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required'      => 'Nama lengkap mahasiswa wajib diisi.',
            'nip_nim.required'   => 'NIM mahasiswa wajib diisi.',
            'nip_nim.unique'     => 'NIM tersebut sudah terdaftar di sistem.',
            'email.required'     => 'Alamat email mahasiswa wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Alamat email tersebut sudah digunakan akun lain.',
            'password.required'  => 'Password wajib ditentukan.',
            'password.min'       => 'Password minimal terdiri dari 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'nip_nim'  => $validated['nip_nim'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'mahasiswa',
        ]);

        return redirect()->route('admin.mahasiswa.index')
            ->with('success', "Mahasiswa {$user->name} (NIM: {$user->nip_nim}) berhasil didaftarkan dan berhak masuk ke sistem.");
    }

    /**
     * Menampilkan formulir edit profil mahasiswa
     */
    public function edit(User $user)
    {
        if (! $user->isMahasiswa()) {
            abort(404, 'Pengguna bukan mahasiswa.');
        }

        return view('admin.mahasiswa.edit', compact('user'));
    }

    /**
     * Memperbarui data mahasiswa
     */
    public function update(Request $request, User $user)
    {
        if (! $user->isMahasiswa()) {
            abort(404, 'Pengguna bukan mahasiswa.');
        }

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'nip_nim'  => ['required', 'string', 'max:50', Rule::unique('users', 'nip_nim')->ignore($user->id)],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required'      => 'Nama lengkap mahasiswa wajib diisi.',
            'nip_nim.required'   => 'NIM mahasiswa wajib diisi.',
            'nip_nim.unique'     => 'NIM tersebut sudah digunakan akun lain.',
            'email.required'     => 'Alamat email mahasiswa wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Alamat email tersebut sudah digunakan akun lain.',
            'password.min'       => 'Password baru minimal terdiri dari 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user->name = $validated['name'];
        $user->nip_nim = $validated['nip_nim'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.mahasiswa.index')
            ->with('success', "Data mahasiswa {$user->name} (NIM: {$user->nip_nim}) berhasil diperbarui.");
    }

    /**
     * Menghapus akun mahasiswa dari sistem
     */
    public function destroy(User $user)
    {
        if (! $user->isMahasiswa()) {
            abort(404, 'Pengguna bukan mahasiswa.');
        }

        // Proteksi integritas data: Mahasiswa yang sudah mengajukan proposal tidak boleh dihapus
        if ($user->proposalsKetua()->exists()) {
            return back()->with('error', "Mahasiswa {$user->name} tidak dapat dihapus karena telah memiliki berkas pengajuan proposal PKM di sistem.");
        }

        $name = $user->name;
        $nim = $user->nip_nim;
        $user->delete();

        return redirect()->route('admin.mahasiswa.index')
            ->with('success', "Akun mahasiswa {$name} (NIM: {$nim}) berhasil dihapus dari sistem.");
    }
}
