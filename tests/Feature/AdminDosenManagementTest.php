<?php

namespace Tests\Feature;

use App\Models\Proposal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminDosenManagementTest extends TestCase
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
            'kepakaran' => 'Artificial Intelligence & IoT',
            'password'  => bcrypt('password'),
        ]);
    }

    /**
     * Admin dapat melihat daftar dosen pembimbing
     */
    public function test_admin_can_view_dosen_index()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dosen.index'));

        $response->assertStatus(200);
        $response->assertSee('Data Dosen Pembimbing PKM');
        $response->assertSee('Dr. Ir. Hendra Wijaya');
        $response->assertSee('198501012010121001');
        $response->assertSee('Artificial Intelligence &amp; IoT', false);
    }

    /**
     * Admin dapat mengakses halaman formulir Add Dosen
     */
    public function test_admin_can_view_add_dosen_form()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dosen.create'));

        $response->assertStatus(200);
        $response->assertSee('Registrasi Dosen Pembimbing Baru');
        $response->assertSee('NIP / NIDN Dosen');
        $response->assertSee('Bidang Kepakaran / Keahlian');
        $response->assertSee('Daftarkan Dosen Pembimbing');
    }

    /**
     * Admin berhasil meregistrasi dosen pembimbing baru
     */
    public function test_admin_can_register_new_dosen()
    {
        $payload = [
            'name'                  => 'Prof. Dr. Ir. Agus Supriyanto, M.Sc.',
            'nip_nim'               => '197903032008011003',
            'email'                 => 'agus.supriyanto@univ.ac.id',
            'kepakaran'             => 'Renewable Energy & Smart Grid',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.dosen.store'), $payload);

        $response->assertRedirect(route('admin.dosen.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name'      => 'Prof. Dr. Ir. Agus Supriyanto, M.Sc.',
            'nip_nim'   => '197903032008011003',
            'email'     => 'agus.supriyanto@univ.ac.id',
            'kepakaran' => 'Renewable Energy & Smart Grid',
            'role'      => 'dosen',
        ]);
    }

    /**
     * Registrasi dosen ditolak jika NIP atau Email sudah terdaftar
     */
    public function test_dosen_registration_fails_on_duplicate_nip_or_email()
    {
        $payload = [
            'name'                  => 'Dosen Duplikat',
            'nip_nim'               => '198501012010121001', // sudah milik Hendra
            'email'                 => 'hendra@test.com', // sudah milik Hendra
            'kepakaran'             => 'Testing',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.dosen.store'), $payload);

        $response->assertSessionHasErrors(['nip_nim', 'email']);
    }

    /**
     * Dosen yang baru didaftarkan dapat login dan masuk ke area bimbingan
     */
    public function test_newly_registered_dosen_can_login()
    {
        $this->actingAs($this->admin)->post(route('admin.dosen.store'), [
            'name'                  => 'Dr. Ratna Dewi, M.Kom.',
            'nip_nim'               => '198808082014022001',
            'email'                 => 'ratna.dewi@univ.ac.id',
            'kepakaran'             => 'Sistem Informasi & E-Commerce',
            'password'              => 'dosenpassword',
            'password_confirmation' => 'dosenpassword',
        ]);

        // Logout admin
        auth()->logout();

        // Login sebagai dosen baru
        $loginResponse = $this->post(route('login.post'), [
            'email'    => 'ratna.dewi@univ.ac.id',
            'password' => 'dosenpassword',
        ]);

        $loginResponse->assertRedirect(route('dosen.proposals.available'));
        $this->assertAuthenticated();
        $this->assertEquals('Dr. Ratna Dewi, M.Kom.', auth()->user()->name);
        $this->assertTrue(auth()->user()->isDosen());
    }

    /**
     * Admin dapat mengedit data dosen dan bidang kepakaran
     */
    public function test_admin_can_update_dosen()
    {
        $response = $this->actingAs($this->admin)->put(route('admin.dosen.update', $this->dosen->id), [
            'name'                  => 'Dr. Ir. Hendra Wijaya, M.Kom., IPM.',
            'nip_nim'               => '198501012010121001',
            'email'                 => 'hendra.wijaya@univ.ac.id',
            'kepakaran'             => 'Deep Learning, Robotics & IoT',
            'password'              => 'newpass123',
            'password_confirmation' => 'newpass123',
        ]);

        $response->assertRedirect(route('admin.dosen.index'));
        $response->assertSessionHas('success');

        $this->dosen->refresh();
        $this->assertEquals('Dr. Ir. Hendra Wijaya, M.Kom., IPM.', $this->dosen->name);
        $this->assertEquals('hendra.wijaya@univ.ac.id', $this->dosen->email);
        $this->assertEquals('Deep Learning, Robotics & IoT', $this->dosen->kepakaran);
        $this->assertTrue(Hash::check('newpass123', $this->dosen->password));
    }

    /**
     * Admin dapat menghapus dosen yang tidak sedang membimbing proposal
     */
    public function test_admin_can_delete_dosen_without_proposals()
    {
        $dosenBaru = User::create([
            'name'      => 'Dosen Tanpa Bimbingan',
            'nip_nim'   => '199001012016011005',
            'email'     => 'tanpa.bimbingan@univ.ac.id',
            'role'      => 'dosen',
            'password'  => bcrypt('password'),
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.dosen.destroy', $dosenBaru->id));

        $response->assertRedirect(route('admin.dosen.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $dosenBaru->id]);
    }

    /**
     * Admin dicegah menghapus dosen yang sedang membimbing usulan proposal
     */
    public function test_admin_cannot_delete_dosen_with_active_proposals()
    {
        // Buat proposal yang sedang dibimbing oleh $this->dosen
        Proposal::create([
            'ketua_id'  => $this->mahasiswa->id,
            'dosen_id'  => $this->dosen->id,
            'judul_pkm' => 'Sistem Pemantauan Tanaman Hidroponik Otomatis',
            'skema_pkm' => 'PKM-PI',
            'file_path' => 'proposals/sample_hidroponik.pdf',
            'status'    => 'sedang_dibimbing',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.dosen.destroy', $this->dosen->id));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->dosen->id]);
    }

    /**
     * Non-admin ditolak saat mengakses rute manajemen dosen
     */
    public function test_non_admin_cannot_access_dosen_management()
    {
        // Mahasiswa ditolak
        $resMahasiswa = $this->actingAs($this->mahasiswa)->get(route('admin.dosen.index'));
        $resMahasiswa->assertStatus(403);

        // Dosen ditolak
        $resDosen = $this->actingAs($this->dosen)->get(route('admin.dosen.create'));
        $resDosen->assertStatus(403);
    }
}
