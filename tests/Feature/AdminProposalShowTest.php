<?php

namespace Tests\Feature;

use App\Models\Proposal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProposalShowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $mahasiswa;

    protected User $dosen;

    protected Proposal $proposal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Administrator PKM',
            'email' => 'admin@test.com',
            'role' => 'admin',
            'nip_nim' => '198001012005011001',
            'password' => bcrypt('password'),
        ]);

        $this->mahasiswa = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@test.com',
            'role' => 'mahasiswa',
            'nip_nim' => '2108101001',
            'password' => bcrypt('password'),
        ]);

        $this->dosen = User::create([
            'name' => 'Dr. Ir. Hendra Wijaya',
            'email' => 'hendra@test.com',
            'role' => 'dosen',
            'nip_nim' => '198501012010121001',
            'kepakaran' => 'Artificial Intelligence & IoT',
            'skema_pkm' => 'All',
            'password' => bcrypt('password'),
        ]);

        $this->proposal = Proposal::create([
            'ketua_id' => $this->mahasiswa->id,
            'dosen_id' => $this->dosen->id,
            'judul_pkm' => 'Smart Greenhouse IoT Berbasis AI',
            'skema_pkm' => 'PKM-KC',
            'file_path' => 'proposals/dummy_test.pdf',
            'status' => 'sedang_dibimbing',
        ]);

        $this->proposal->members()->create([
            'nama_anggota' => 'Ahmad Fauzi',
            'nim_anggota' => '2108101002',
            'role_anggota' => 'Anggota 1',
            'jobdesk' => 'Hardware developer',
        ]);

        $this->proposal->documents()->create([
            'version' => 1,
            'file_path' => 'proposals/dummy_test.pdf',
            'catatan_revisi' => 'Naskah awal usulan.',
        ]);

        $this->proposal->reviews()->create([
            'dosen_id' => $this->dosen->id,
            'halaman' => 3,
            'catatan_review' => 'Perbaiki diagram arsitektur sensor.',
            'status_bagian' => 'perlu_perbaikan',
            'is_resolved' => false,
        ]);
    }

    public function test_guest_is_redirected_from_admin_proposal_show(): void
    {
        $response = $this->get(route('admin.proposals.show', $this->proposal));

        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_admin_proposal_show(): void
    {
        $this->actingAs($this->mahasiswa)
            ->get(route('admin.proposals.show', $this->proposal))
            ->assertForbidden();

        $this->actingAs($this->dosen)
            ->get(route('admin.proposals.show', $this->proposal))
            ->assertForbidden();
    }

    public function test_admin_can_view_proposal_detail_blade_view(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.proposals.show', $this->proposal));

        $response->assertOk();
        $response->assertViewIs('admin.proposal_detail');
        $response->assertSee('Smart Greenhouse IoT Berbasis AI');
        $response->assertSee('Budi Santoso');
        $response->assertSee('Dr. Ir. Hendra Wijaya');
        $response->assertSee('Ahmad Fauzi');
        $response->assertSee('Perbaiki diagram arsitektur sensor.');
    }

    public function test_admin_can_fetch_proposal_detail_via_ajax_json(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.proposals.show', $this->proposal));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'proposal' => [
                'id' => $this->proposal->id,
                'judul_pkm' => 'Smart Greenhouse IoT Berbasis AI',
            ],
        ]);
    }
}
