<?php

namespace Tests\Feature;

use App\Models\Proposal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminMahasiswaManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $mahasiswa;
    protected User $dosen;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name'     => 'Administrator PKM',
            'email'    => 'admin@test.com',
            'role'     => 'admin',
            'nip_nim'  => '198001012005011001',
            'password' => bcrypt('password'),
        ]);

        $this->mahasiswa = User::create([
            'name'     => 'Budi Santoso',
            'email'    => 'budi@test.com',
            'role'     => 'mahasiswa',
            'nip_nim'  => '2108101001',
            'password' => bcrypt('password'),
        ]);

        $this->dosen = User::create([
            'name'      => 'Dr. Ir. Hendra Wijaya',
            'email'     => 'hendra@test.com',
            'role'      => 'dosen',
            'nip_nim'   => '198501012010121001',
            'kepakaran' => 'Artificial Intelligence',
            'password'  => bcrypt('password'),
        ]);
    }

    /**
     * Admin dapat melihat daftar mahasiswa
     */
    public function test_admin_can_view_mahasiswa_index()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.mahasiswa.index'));

        $response->assertStatus(200);
        $response->assertSee('Data Mahasiswa Berhak Masuk Sistem');
        $response->assertSee('Budi Santoso');
        $response->assertSee('2108101001');
    }

    /**
     * Admin dapat mengakses halaman Add Mahasiswa
     */
    public function test_admin_can_view_add_mahasiswa_form()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.mahasiswa.create'));

        $response->assertStatus(200);
        $response->assertSee('Registrasi Mahasiswa Baru');
        $response->assertSee('NIM (Nomor Induk Mahasiswa)');
        $response->assertSee('Daftarkan Mahasiswa');
    }

    /**
     * Admin berhasil meregistrasi mahasiswa baru
     */
    public function test_admin_can_register_new_mahasiswa()
    {
        $payload = [
            'name'                  => 'Dewi Lestari',
            'nip_nim'               => '2208101099',
            'email'                 => 'dewi@student.ac.id',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.mahasiswa.store'), $payload);

        $response->assertRedirect(route('admin.mahasiswa.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name'    => 'Dewi Lestari',
            'nip_nim' => '2208101099',
            'email'   => 'dewi@student.ac.id',
            'role'    => 'mahasiswa',
        ]);
    }

    /**
     * Registrasi gagal jika NIM atau Email sudah terdaftar
     */
    public function test_registration_fails_on_duplicate_nim_or_email()
    {
        $payload = [
            'name'                  => 'Mahasiswa Duplikat',
            'nip_nim'               => '2108101001', // sudah milik Budi
            'email'                 => 'budi@test.com', // sudah milik Budi
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.mahasiswa.store'), $payload);

        $response->assertSessionHasErrors(['nip_nim', 'email']);
    }

    /**
     * Mahasiswa yang baru diregistrasi oleh admin dapat login ke sistem
     */
    public function test_newly_registered_mahasiswa_can_login()
    {
        $this->actingAs($this->admin)->post(route('admin.mahasiswa.store'), [
            'name'                  => 'Ahmad Fauzi',
            'nip_nim'               => '2208101050',
            'email'                 => 'ahmad@student.ac.id',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Logout admin
        auth()->logout();

        // Coba login sebagai mahasiswa baru
        $loginResponse = $this->post(route('login.post'), [
            'email'    => 'ahmad@student.ac.id',
            'password' => 'password123',
        ]);

        $loginResponse->assertRedirect(route('mahasiswa.dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('Ahmad Fauzi', auth()->user()->name);
        $this->assertTrue(auth()->user()->isMahasiswa());
    }

    /**
     * Admin dapat mengedit data mahasiswa
     */
    public function test_admin_can_update_mahasiswa()
    {
        $response = $this->actingAs($this->admin)->put(route('admin.mahasiswa.update', $this->mahasiswa->id), [
            'name'                  => 'Budi Santoso Update',
            'nip_nim'               => '2108101001',
            'email'                 => 'budi.baru@test.com',
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('admin.mahasiswa.index'));
        $response->assertSessionHas('success');

        $this->mahasiswa->refresh();
        $this->assertEquals('Budi Santoso Update', $this->mahasiswa->name);
        $this->assertEquals('budi.baru@test.com', $this->mahasiswa->email);
        $this->assertTrue(Hash::check('newpassword123', $this->mahasiswa->password));
    }

    /**
     * Admin dapat menghapus mahasiswa yang belum memiliki proposal
     */
    public function test_admin_can_delete_mahasiswa_without_proposals()
    {
        $user = User::create([
            'name'     => 'Mahasiswa Tanpa Proposal',
            'nip_nim'  => '2108109999',
            'email'    => 'tanpa.proposal@test.com',
            'role'     => 'mahasiswa',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.mahasiswa.destroy', $user->id));

        $response->assertRedirect(route('admin.mahasiswa.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /**
     * Admin dicegah menghapus mahasiswa yang sudah memiliki riwayat usulan proposal
     */
    public function test_admin_cannot_delete_mahasiswa_with_proposals()
    {
        // Buat proposal untuk $this->mahasiswa
        Proposal::create([
            'ketua_id'  => $this->mahasiswa->id,
            'judul_pkm' => 'Inovasi Sistem IoT Berkelanjutan',
            'skema_pkm' => 'PKM-KC',
            'file_path' => 'proposals/sample.pdf',
            'status'    => 'diajukan',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.mahasiswa.destroy', $this->mahasiswa->id));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->mahasiswa->id]);
    }

    /**
     * Non-admin ditolak saat mengakses rute manajemen mahasiswa
     */
    public function test_non_admin_cannot_access_mahasiswa_management()
    {
        // Mahasiswa ditolak
        $resMahasiswa = $this->actingAs($this->mahasiswa)->get(route('admin.mahasiswa.index'));
        $resMahasiswa->assertStatus(403);

        // Dosen ditolak
        $resDosen = $this->actingAs($this->dosen)->get(route('admin.mahasiswa.create'));
        $resDosen->assertStatus(403);
    }
}
