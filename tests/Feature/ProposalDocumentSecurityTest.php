<?php

namespace Tests\Feature;

use App\Models\Proposal;
use App\Models\ProposalDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProposalDocumentSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $mahasiswa1;

    protected User $mahasiswa2;

    protected User $dosenMatching;

    protected User $dosenMismatched;

    protected User $admin;

    protected Proposal $unassignedProposal;

    protected Proposal $assignedProposal;

    protected ProposalDocument $documentV1;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');

        $this->mahasiswa1 = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@test.com',
            'role' => 'mahasiswa',
            'nip_nim' => '2108101001',
            'password' => bcrypt('password'),
        ]);

        $this->mahasiswa2 = User::create([
            'name' => 'Siti Aminah',
            'email' => 'siti@test.com',
            'role' => 'mahasiswa',
            'nip_nim' => '2108101002',
            'password' => bcrypt('password'),
        ]);

        $this->dosenMatching = User::create([
            'name' => 'Dr. Ir. Hendra Wijaya',
            'email' => 'hendra@test.com',
            'role' => 'dosen',
            'nip_nim' => '198501012010121001',
            'kepakaran' => 'Artificial Intelligence',
            'skema_pkm' => 'PKM-KC',
            'password' => bcrypt('password'),
        ]);

        $this->dosenMismatched = User::create([
            'name' => 'Prof. Dr. Siti Rahmawati',
            'email' => 'siti_dosen@test.com',
            'role' => 'dosen',
            'nip_nim' => '198702022012122002',
            'kepakaran' => 'Kewirausahaan',
            'skema_pkm' => 'PKM-K',
            'password' => bcrypt('password'),
        ]);

        $this->admin = User::create([
            'name' => 'Administrator PKM',
            'email' => 'admin@test.com',
            'role' => 'admin',
            'nip_nim' => '198001012005011001',
            'password' => bcrypt('password'),
        ]);

        // Simpan berkas dummy di storage private (local)
        $pdfPath1 = 'proposals/dummy_pkm_kc.pdf';
        $pdfPath2 = 'proposals/dummy_assigned.pdf';
        Storage::disk('local')->put($pdfPath1, '%PDF-1.4 sample content');
        Storage::disk('local')->put($pdfPath2, '%PDF-1.4 assigned content');

        // Proposal 1: Belum ada pembimbing (status diajukan, skema PKM-KC)
        $this->unassignedProposal = Proposal::create([
            'ketua_id' => $this->mahasiswa1->id,
            'dosen_id' => null,
            'judul_pkm' => 'IoT Water Monitoring',
            'skema_pkm' => 'PKM-KC',
            'file_path' => $pdfPath1,
            'status' => 'diajukan',
        ]);

        $this->documentV1 = $this->unassignedProposal->documents()->create([
            'version' => 1,
            'file_path' => $pdfPath1,
            'catatan_revisi' => 'Naskah awal usulan.',
        ]);

        // Proposal 2: Sudah dibimbing oleh dosenMatching
        $this->assignedProposal = Proposal::create([
            'ketua_id' => $this->mahasiswa1->id,
            'dosen_id' => $this->dosenMatching->id,
            'judul_pkm' => 'AI Smart Farming',
            'skema_pkm' => 'PKM-KC',
            'file_path' => $pdfPath2,
            'status' => 'sedang_dibimbing',
        ]);
    }

    public function test_guest_cannot_access_proposal_file(): void
    {
        $response = $this->get(route('proposals.file', $this->unassignedProposal));

        $response->assertRedirect(route('login'));
    }

    public function test_unauthorized_student_cannot_access_other_student_proposal_file(): void
    {
        $this->actingAs($this->mahasiswa2)
            ->get(route('proposals.file', $this->unassignedProposal))
            ->assertForbidden();
    }

    public function test_owner_student_can_stream_proposal_file(): void
    {
        $response = $this->actingAs($this->mahasiswa1)
            ->get(route('proposals.file', $this->unassignedProposal));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_dosen_with_matching_skema_can_preview_unassigned_proposal(): void
    {
        $response = $this->actingAs($this->dosenMatching)
            ->get(route('proposals.file', $this->unassignedProposal));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_dosen_with_mismatched_skema_cannot_preview_unassigned_proposal(): void
    {
        $this->actingAs($this->dosenMismatched)
            ->get(route('proposals.file', $this->unassignedProposal))
            ->assertForbidden();
    }

    public function test_assigned_dosen_can_stream_proposal_file(): void
    {
        $response = $this->actingAs($this->dosenMatching)
            ->get(route('proposals.file', $this->assignedProposal));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_other_dosen_cannot_stream_assigned_proposal(): void
    {
        $this->actingAs($this->dosenMismatched)
            ->get(route('proposals.file', $this->assignedProposal))
            ->assertForbidden();
    }

    public function test_admin_can_stream_any_proposal_file(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('proposals.file', $this->unassignedProposal));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_authorized_user_can_stream_specific_version_document(): void
    {
        $response = $this->actingAs($this->mahasiswa1)
            ->get(route('proposals.documents.show', [$this->unassignedProposal, $this->documentV1]));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_missing_physical_file_returns_404(): void
    {
        $brokenProposal = Proposal::create([
            'ketua_id' => $this->mahasiswa1->id,
            'dosen_id' => null,
            'judul_pkm' => 'Missing File Proposal',
            'skema_pkm' => 'PKM-KC',
            'file_path' => 'proposals/non_existent_file.pdf',
            'status' => 'diajukan',
        ]);

        $this->actingAs($this->mahasiswa1)
            ->get(route('proposals.file', $brokenProposal))
            ->assertNotFound();
    }
}
