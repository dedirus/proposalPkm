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
        $response->assertSee('Jenis Skema PKM Bimbingan');
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
            'skema_pkm'             => 'PKM-RE',
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
            'skema_pkm' => 'PKM-RE',
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
            'skema_pkm'             => 'All',
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
            'skema_pkm'             => 'All',
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
     * Admin dapat mengedit data dosen, bidang kepakaran, dan skema pkm
     */
    public function test_admin_can_update_dosen()
    {
        $response = $this->actingAs($this->admin)->put(route('admin.dosen.update', $this->dosen->id), [
            'name'                  => 'Dr. Ir. Hendra Wijaya, M.Kom., IPM.',
            'nip_nim'               => '198501012010121001',
            'email'                 => 'hendra.wijaya@univ.ac.id',
            'kepakaran'             => 'Deep Learning, Robotics & IoT',
            'skema_pkm'             => 'PKM-KC',
            'password'              => 'newpass123',
            'password_confirmation' => 'newpass123',
        ]);

        $response->assertRedirect(route('admin.dosen.index'));
        $response->assertSessionHas('success');

        $this->dosen->refresh();
        $this->assertEquals('Dr. Ir. Hendra Wijaya, M.Kom., IPM.', $this->dosen->name);
        $this->assertEquals('hendra.wijaya@univ.ac.id', $this->dosen->email);
        $this->assertEquals('Deep Learning, Robotics & IoT', $this->dosen->kepakaran);
        $this->assertEquals('PKM-KC', $this->dosen->skema_pkm);
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

    /**
     * Dosen dengan skema 'All' dapat melihat proposal dari berbagai skema
     */
    public function test_dosen_with_all_skema_can_view_all_available_proposals()
    {
        $this->dosen->update(['skema_pkm' => 'All']);

        Proposal::create([
            'ketua_id'  => $this->mahasiswa->id,
            'judul_pkm' => 'Proposal Skema RE',
            'skema_pkm' => 'PKM-RE (Riset Eksakta)',
            'file_path' => 'proposals/re.pdf',
            'status'    => 'diajukan',
        ]);

        Proposal::create([
            'ketua_id'  => $this->mahasiswa->id,
            'judul_pkm' => 'Proposal Skema Kewirausahaan',
            'skema_pkm' => 'PKM-K (Kewirausahaan)',
            'file_path' => 'proposals/k.pdf',
            'status'    => 'diajukan',
        ]);

        $response = $this->actingAs($this->dosen)->get(route('dosen.proposals.available'));

        $response->assertStatus(200);
        $response->assertSee('Proposal Skema RE');
        $response->assertSee('Proposal Skema Kewirausahaan');
    }

    /**
     * Dosen dengan skema spesifik (misal PKM-K) hanya dapat melihat proposal skema PKM-K
     */
    public function test_dosen_with_specific_skema_only_sees_matching_proposals()
    {
        $this->dosen->update(['skema_pkm' => 'PKM-K']);

        Proposal::create([
            'ketua_id'  => $this->mahasiswa->id,
            'judul_pkm' => 'Proposal Skema Karsa Cipta',
            'skema_pkm' => 'PKM-KC (Karsa Cipta)',
            'file_path' => 'proposals/kc.pdf',
            'status'    => 'diajukan',
        ]);

        Proposal::create([
            'ketua_id'  => $this->mahasiswa->id,
            'judul_pkm' => 'Proposal Skema Bisnis Kopi',
            'skema_pkm' => 'PKM-K (Kewirausahaan)',
            'file_path' => 'proposals/kopi.pdf',
            'status'    => 'diajukan',
        ]);

        $response = $this->actingAs($this->dosen)->get(route('dosen.proposals.available'));

        $response->assertStatus(200);
        $response->assertSee('Proposal Skema Bisnis Kopi');
        $response->assertDontSee('Proposal Skema Karsa Cipta');
    }

    /**
     * Dosen dicegah saat mencoba mengklaim proposal di luar skema yang ditugaskan
     */
    public function test_dosen_cannot_claim_proposal_outside_their_skema()
    {
        $this->dosen->update(['skema_pkm' => 'PKM-K']);

        $proposalLuarSkema = Proposal::create([
            'ketua_id'  => $this->mahasiswa->id,
            'judul_pkm' => 'Robot Pemadam Api Cerdas',
            'skema_pkm' => 'PKM-KC',
            'file_path' => 'proposals/robot.pdf',
            'status'    => 'diajukan',
        ]);

        $response = $this->actingAs($this->dosen)->post(route('dosen.proposals.claim', $proposalLuarSkema->id));

        $response->assertRedirect(route('dosen.proposals.available'));
        $response->assertSessionHas('error');

        $proposalLuarSkema->refresh();
        $this->assertNull($proposalLuarSkema->dosen_id);
    }

    /**
     * Dosen berhasil mengklaim proposal yang sesuai skemanya
     */
    public function test_dosen_can_claim_proposal_matching_their_skema()
    {
        $this->dosen->update(['skema_pkm' => 'PKM-K']);

        $proposalCocok = Proposal::create([
            'ketua_id'  => $this->mahasiswa->id,
            'judul_pkm' => 'Usaha Camilan Sehat Jamur Crispy',
            'skema_pkm' => 'PKM-K (Kewirausahaan)',
            'file_path' => 'proposals/jamur.pdf',
            'status'    => 'diajukan',
        ]);

        $response = $this->actingAs($this->dosen)->post(route('dosen.proposals.claim', $proposalCocok->id));

        $response->assertRedirect(route('dosen.bimbingan.show', $proposalCocok->id));
        $response->assertSessionHas('success');

        $proposalCocok->refresh();
        $this->assertEquals($this->dosen->id, $proposalCocok->dosen_id);
        $this->assertEquals('sedang_dibimbing', $proposalCocok->status);
    }
}
