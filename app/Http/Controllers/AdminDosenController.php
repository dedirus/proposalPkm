<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminDosenController extends Controller
{
    /**
     * Menampilkan daftar dosen pembimbing yang terdaftar di sistem
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'dosen')->withCount('proposalsBimbingan');

        // Pencarian Berdasarkan Nama, NIP, Email, atau Bidang Kepakaran
        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('nip_nim', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('kepakaran', 'LIKE', "%{$search}%");
            });
        }

        $dosens = $query->latest()->paginate(15)->withQueryString();
        $totalDosen = User::where('role', 'dosen')->count();

        return view('admin.dosen.index', compact('dosens', 'totalDosen'));
    }

    /**
     * Menampilkan formulir pendaftaran Dosen Pembimbing baru (Setting -> Add Dosen)
     */
    public function create()
    {
        return view('admin.dosen.create');
    }

    /**
     * Menyimpan data pendaftaran Dosen Pembimbing baru ke sistem
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'nip_nim'   => ['required', 'string', 'max:50', 'unique:users,nip_nim'],
            'email'     => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'kepakaran' => ['nullable', 'string', 'max:255'],
            'password'  => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required'      => 'Nama lengkap beserta gelar dosen wajib diisi.',
            'nip_nim.required'   => 'NIP / NIDN dosen wajib diisi.',
            'nip_nim.unique'     => 'NIP / NIDN tersebut sudah terdaftar di sistem.',
            'email.required'     => 'Alamat email dosen wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Alamat email tersebut sudah digunakan oleh akun lain.',
            'password.required'  => 'Password wajib ditentukan.',
            'password.min'       => 'Password minimal terdiri dari 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::create([
            'name'      => $validated['name'],
            'nip_nim'   => $validated['nip_nim'],
            'email'     => $validated['email'],
            'kepakaran' => $validated['kepakaran'] ?? null,
            'password'  => Hash::make($validated['password']),
            'role'      => 'dosen',
        ]);

        return redirect()->route('admin.dosen.index')
            ->with('success', "Dosen Pembimbing {$user->name} (NIP: {$user->nip_nim}) berhasil didaftarkan dan berhak membimbing proposal PKM.");
    }

    /**
     * Menampilkan formulir edit data Dosen Pembimbing
     */
    public function edit(User $user)
    {
        if (! $user->isDosen()) {
            abort(404, 'Pengguna bukan dosen.');
        }

        return view('admin.dosen.edit', compact('user'));
    }

    /**
     * Memperbarui data Dosen Pembimbing
     */
    public function update(Request $request, User $user)
    {
        if (! $user->isDosen()) {
            abort(404, 'Pengguna bukan dosen.');
        }

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'nip_nim'   => ['required', 'string', 'max:50', Rule::unique('users', 'nip_nim')->ignore($user->id)],
            'email'     => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'kepakaran' => ['nullable', 'string', 'max:255'],
            'password'  => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required'      => 'Nama lengkap beserta gelar dosen wajib diisi.',
            'nip_nim.required'   => 'NIP / NIDN dosen wajib diisi.',
            'nip_nim.unique'     => 'NIP / NIDN tersebut sudah digunakan oleh akun lain.',
            'email.required'     => 'Alamat email dosen wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Alamat email tersebut sudah digunakan oleh akun lain.',
            'password.min'       => 'Password baru minimal terdiri dari 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user->name = $validated['name'];
        $user->nip_nim = $validated['nip_nim'];
        $user->email = $validated['email'];
        $user->kepakaran = $validated['kepakaran'] ?? null;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.dosen.index')
            ->with('success', "Data Dosen Pembimbing {$user->name} (NIP: {$user->nip_nim}) berhasil diperbarui.");
    }

    /**
     * Menghapus akun Dosen Pembimbing dari sistem
     */
    public function destroy(User $user)
    {
        if (! $user->isDosen()) {
            abort(404, 'Pengguna bukan dosen.');
        }

        // Proteksi integritas data: Dosen yang sedang membimbing usulan proposal tidak boleh dihapus
        if ($user->proposalsBimbingan()->exists()) {
            return back()->with('error', "Dosen {$user->name} tidak dapat dihapus karena sedang membimbing usulan proposal PKM mahasiswa.");
        }

        $name = $user->name;
        $nip = $user->nip_nim;
        $user->delete();

        return redirect()->route('admin.dosen.index')
            ->with('success', "Akun Dosen Pembimbing {$name} (NIP: {$nip}) berhasil dihapus dari sistem.");
    }
}
