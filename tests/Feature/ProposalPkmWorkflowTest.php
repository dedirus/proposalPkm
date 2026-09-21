<?php

namespace Tests\Feature;

use App\Models\Proposal;
use App\Models\ProposalMember;
use App\Models\ProposalReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProposalPkmWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $mahasiswa;
    protected User $dosen1;
    protected User $dosen2;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Siapkan akun pengujian
        $this->mahasiswa = User::create([
            'name'     => 'Budi Santoso',
            'email'    => 'budi@test.com',
            'role'     => 'mahasiswa',
            'nip_nim'  => '2108101001',
            'password' => bcrypt('password'),
        ]);

        $this->dosen1 = User::create([
            'name'      => 'Dr. Hendra Wijaya',
            'email'     => 'hendra@test.com',
            'role'      => 'dosen',
            'nip_nim'   => '198501012010121001',
            'kepakaran' => 'Artificial Intelligence & IoT',
            'password'  => bcrypt('password'),
        ]);

        $this->dosen2 = User::create([
            'name'      => 'Prof. Siti Rahmawati',
            'email'     => 'siti@test.com',
            'role'      => 'dosen',
            'nip_nim'   => '198702022012122002',
            'kepakaran' => 'Kewirausahaan & Bisnis Digital',
            'password'  => bcrypt('password'),
        ]);

        $this->admin = User::create([
            'name'      => 'Administrator PKM',
            'email'     => 'admin@test.com',
            'role'      => 'admin',
            'nip_nim'   => '198001012005011001',
            'kepakaran' => 'Manajemen PKM',
            'password'  => bcrypt('password'),
        ]);
    }

    /**
     * Test 1: Mahasiswa dapat mengunggah proposal PDF dan bulk-insert anggota tim
     */
    public function test_mahasiswa_can_upload_proposal_and_bulk_insert_members(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('proposal_pkm.pdf', 500, 'application/pdf');

        $payload = [
            'judul_pkm'     => 'Implementasi IoT untuk Smart Farming',
            'skema_pkm'     => 'PKM-KC (Karsa Cipta)',
            'proposal_file' => $file,
            'members'       => [
                [
                    'nama_anggota' => 'Ahmad Fauzi',
                    'nim_anggota'  => '2108101002',
                    'role_anggota' => 'Anggota 1',
                    'jobdesk'      => 'Merancang sensor tanah dan NodeMCU.',
                ],
                [
                    'nama_anggota' => 'Dewi Anggraini',
                    'nim_anggota'  => '2108101003',
                    'role_anggota' => 'Anggota 2',
                    'jobdesk'      => 'Membangun dashboard web pemantauan.',
                ],
            ],
        ];

        $response = $this->actingAs($this->mahasiswa)
            ->post(route('mahasiswa.proposal.store'), $payload);

        $response->assertRedirect(route('mahasiswa.dashboard'));
        $response->assertSessionHas('success');

        // Pastikan proposal tersimpan di database
        $this->assertDatabaseHas('proposals', [
            'ketua_id'  => $this->mahasiswa->id,
            'judul_pkm' => 'Implementasi IoT untuk Smart Farming',
            'dosen_id'  => null,
            'status'    => 'diajukan',
        ]);

        // Pastikan kedua anggota tim berhasil di-bulk insert
        $this->assertDatabaseCount('proposal_members', 2);
        $this->assertDatabaseHas('proposal_members', [
            'nama_anggota' => 'Ahmad Fauzi',
            'nim_anggota'  => '2108101002',
        ]);
    }

    /**
     * Test 2: Dashboard Mahasiswa menampilkan "Mencari Pembimbing" jika dosen_id masih NULL
     */
    public function test_dashboard_shows_mencari_pembimbing_when_dosen_id_is_null(): void
    {
        $proposal = Proposal::create([
            'ketua_id'  => $this->mahasiswa->id,
            'dosen_id'  => null,
            'judul_pkm' => 'Prototipe Kursi Roda Otomatis',
            'skema_pkm' => 'PKM-KC',
            'file_path' => 'proposals/dummy.pdf',
            'status'    => 'diajukan',
        ]);

        $this->assertEquals('Mencari Pembimbing', $proposal->status_pembimbing);

        $response = $this->actingAs($this->mahasiswa)
            ->get(route('mahasiswa.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Mencari Pembimbing');
    }

    /**
     * Test 3: Dosen dapat melihat daftar proposal yang dosen_id-nya masih NULL
     */
    public function test_dosen_can_view_available_proposals(): void
    {
        Proposal::create([
            'ketua_id'  => $this->mahasiswa->id,
            'dosen_id'  => null,
            'judul_pkm' => 'Pengembangan Aplikasi Deteksi Dini Stunting',
            'skema_pkm' => 'PKM-PM',
            'file_path' => 'proposals/stunting.pdf',
            'status'    => 'diajukan',
        ]);

        $response = $this->actingAs($this->dosen1)
            ->get(route('dosen.proposals.available'));

        $response->assertStatus(200);
        $response->assertSee('Pengembangan Aplikasi Deteksi Dini Stunting');
    }

    /**
     * Test 4: Dosen mengklaim proposal berhasil dan status berubah menjadi sedang_dibimbing
     */
    public function test_dosen_can_claim_proposal(): void
    {
        $proposal = Proposal::create([
            'ketua_id'  => $this->mahasiswa->id,
            'dosen_id'  => null,
            'judul_pkm' => 'Pengembangan Aplikasi Deteksi Dini Stunting',
            'skema_pkm' => 'PKM-PM',
            'file_path' => 'proposals/stunting.pdf',
            'status'    => 'diajukan',
        ]);

        $response = $this->actingAs($this->dosen1)
            ->post(route('dosen.proposals.claim', $proposal->id));

        $response->assertRedirect(route('dosen.bimbingan.show', $proposal->id));

        $proposal->refresh();
        $this->assertEquals($this->dosen1->id, $proposal->dosen_id);
        $this->assertEquals('sedang_dibimbing', $proposal->status);
    }

    /**
     * Test 5: Perlindungan Race Condition / Klaim ganda oleh dosen lain ditolak
     */
    public function test_dosen_cannot_claim_already_claimed_proposal(): void
    {
        // Proposal sudah diklaim oleh dosen 1
        $proposal = Proposal::create([
            'ketua_id'  => $this->mahasiswa->id,
            'dosen_id'  => $this->dosen1->id,
            'judul_pkm' => 'Pengembangan Drone Pemadam Kebakaran',
            'skema_pkm' => 'PKM-KC',
            'file_path' => 'proposals/drone.pdf',
            'status'    => 'sedang_dibimbing',
        ]);

        // Dosen 2 mencoba mengklaim proposal yang sama
        $response = $this->actingAs($this->dosen2)
            ->post(route('dosen.proposals.claim', $proposal->id));

        $response->assertRedirect(route('dosen.proposals.available'));
        $response->assertSessionHas('error');

        // Pastikan dosen_id tidak berubah
        $proposal->refresh();
        $this->assertEquals($this->dosen1->id, $proposal->dosen_id);
    }

    /**
     * Test 6: Dosen pembimbing dapat menginput review komentar via JSON/AJAX
     */
    public function test_dosen_can_store_review_comment(): void
    {
        $proposal = Proposal::create([
            'ketua_id'  => $this->mahasiswa->id,
            'dosen_id'  => $this->dosen1->id,
            'judul_pkm' => 'Pengembangan Drone Pemadam Kebakaran',
            'skema_pkm' => 'PKM-KC',
            'file_path' => 'proposals/drone.pdf',
            'status'    => 'sedang_dibimbing',
        ]);

        $payload = [
            'halaman'        => 5,
            'status_bagian'  => 'perlu_perbaikan',
            'catatan_review' => 'Perbaiki perincian biaya anggaran motor brushless.',
        ];

        $response = $this->actingAs($this->dosen1)
            ->postJson(route('dosen.bimbingan.review.store', $proposal->id), $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'data'    => [
                'halaman'        => 5,
                'status_bagian'  => 'perlu_perbaikan',
                'catatan_review' => 'Perbaiki perincian biaya anggaran motor brushless.',
            ],
        ]);

        $this->assertDatabaseHas('proposal_reviews', [
            'proposal_id'    => $proposal->id,
            'dosen_id'       => $this->dosen1->id,
            'halaman'        => 5,
            'status_bagian'  => 'perlu_perbaikan',
        ]);
    }

    /**
     * Test 7: Dashboard mahasiswa menampilkan nama dosen setelah proposal diklaim
     */
    public function test_dashboard_shows_dosen_name_after_claimed(): void
    {
        $proposal = Proposal::create([
            'ketua_id'  => $this->mahasiswa->id,
            'dosen_id'  => $this->dosen1->id,
            'judul_pkm' => 'Sistem AI Pemilah Sampah Organik',
            'skema_pkm' => 'PKM-RE',
            'file_path' => 'proposals/sampah.pdf',
            'status'    => 'sedang_dibimbing',
        ]);

        $this->assertEquals('Sudah Ada Pembimbing: Dr. Hendra Wijaya', $proposal->status_pembimbing);

        $response = $this->actingAs($this->mahasiswa)
            ->get(route('mahasiswa.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Sudah Ada Pembimbing: Dr. Hendra Wijaya');
    }

    /**
     * Test 8: Dosen dapat memperbarui (edit) catatan review miliknya via AJAX
     */
    public function test_dosen_can_update_review_comment(): void
    {
        $proposal = Proposal::create([
            'ketua_id'  => $this->mahasiswa->id,
            'dosen_id'  => $this->dosen1->id,
            'judul_pkm' => 'Sistem Deteksi Hama',
            'skema_pkm' => 'PKM-KC',
            'file_path' => 'proposals/hama.pdf',
            'status'    => 'sedang_dibimbing',
        ]);

        $review = ProposalReview::create([
            'proposal_id'    => $proposal->id,
            'dosen_id'       => $this->dosen1->id,
            'halaman'        => 3,
            'status_bagian'  => 'perlu_perbaikan',
            'catatan_review' => 'Catatan awal sebelum diedit',
        ]);

        $payload = [
            'halaman'        => 4,
            'status_bagian'  => 'oke',
            'catatan_review' => 'Catatan telah diperbaiki dan diperbarui oleh dosen.',
        ];

        $response = $this->actingAs($this->dosen1)
            ->putJson(route('dosen.reviews.update', $review->id), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data'    => [
                'id'             => $review->id,
                'halaman'        => 4,
                'status_bagian'  => 'oke',
                'catatan_review' => 'Catatan telah diperbaiki dan diperbarui oleh dosen.',
            ],
        ]);

        $this->assertDatabaseHas('proposal_reviews', [
            'id'             => $review->id,
            'halaman'        => 4,
            'status_bagian'  => 'oke',
            'catatan_review' => 'Catatan telah diperbaiki dan diperbarui oleh dosen.',
        ]);
    }

    /**
     * Test 9: Dosen dapat menghapus catatan review miliknya via AJAX
     */
    public function test_dosen_can_delete_review_comment(): void
    {
        $proposal = Proposal::create([
            'ketua_id'  => $this->mahasiswa->id,
            'dosen_id'  => $this->dosen1->id,
            'judul_pkm' => 'Sistem Deteksi Hama',
            'skema_pkm' => 'PKM-KC',
            'file_path' => 'proposals/hama.pdf',
            'status'    => 'sedang_dibimbing',
        ]);

        $review = ProposalReview::create([
            'proposal_id'    => $proposal->id,
            'dosen_id'       => $this->dosen1->id,
            'halaman'        => 3,
            'status_bagian'  => 'perlu_perbaikan',
            'catatan_review' => 'Catatan yang akan dihapus',
        ]);

        $response = $this->actingAs($this->dosen1)
            ->deleteJson(route('dosen.reviews.destroy', $review->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success'   => true,
            'review_id' => $review->id,
        ]);

        $this->assertDatabaseMissing('proposal_reviews', [
            'id' => $review->id,
        ]);
    }

    /**
     * Test 10: Dosen lain tidak berhak mengedit atau menghapus catatan review milik dosen lain
     */
    public function test_dosen_cannot_modify_other_dosen_review(): void
    {
        $proposal = Proposal::create([
            'ketua_id'  => $this->mahasiswa->id,
            'dosen_id'  => $this->dosen1->id,
            'judul_pkm' => 'Sistem Deteksi Hama',
            'skema_pkm' => 'PKM-KC',
            'file_path' => 'proposals/hama.pdf',
            'status'    => 'sedang_dibimbing',
        ]);

        $review = ProposalReview::create([
            'proposal_id'    => $proposal->id,
            'dosen_id'       => $this->dosen1->id,
            'halaman'        => 3,
            'status_bagian'  => 'perlu_perbaikan',
            'catatan_review' => 'Catatan milik dosen 1',
        ]);

        // Dosen 2 mencoba update
        $responseEdit = $this->actingAs($this->dosen2)
            ->putJson(route('dosen.reviews.update', $review->id), [
                'halaman'        => 1,
                'status_bagian'  => 'oke',
                'catatan_review' => 'Mencoba merubah catatan dosen 1',
            ]);
        $responseEdit->assertStatus(403);

        // Dosen 2 mencoba hapus
        $responseDelete = $this->actingAs($this->dosen2)
            ->deleteJson(route('dosen.reviews.destroy', $review->id));
        $responseDelete->assertStatus(403);
    }

    /**
     * Test 11: Mahasiswa ketua tim dapat men-toggle status perbaikan (is_resolved)
     */
    public function test_mahasiswa_can_toggle_resolve_status(): void
    {
        $proposal = Proposal::create([
            'ketua_id'  => $this->mahasiswa->id,
            'dosen_id'  => $this->dosen1->id,
            'judul_pkm' => 'Sistem Deteksi Hama',
            'skema_pkm' => 'PKM-KC',
            'file_path' => 'proposals/hama.pdf',
            'status'    => 'sedang_dibimbing',
        ]);

        $review = ProposalReview::create([
            'proposal_id'    => $proposal->id,
            'dosen_id'       => $this->dosen1->id,
            'halaman'        => 3,
            'status_bagian'  => 'perlu_perbaikan',
            'catatan_review' => 'Perbaiki latar belakang masalah.',
            'is_resolved'    => false,
        ]);

        // Toggle pertama: menjadi true (selesai diperbaiki)
        $response1 = $this->actingAs($this->mahasiswa)
            ->patchJson(route('mahasiswa.reviews.toggle-resolve', $review->id));

        $response1->assertStatus(200);
        $response1->assertJson([
            'success'     => true,
            'is_resolved' => true,
        ]);

        $review->refresh();
        $this->assertTrue($review->is_resolved);

        // Toggle kedua: membatalkan tanda (kembali false)
        $response2 = $this->actingAs($this->mahasiswa)
            ->patchJson(route('mahasiswa.reviews.toggle-resolve', $review->id));

        $response2->assertStatus(200);
        $response2->assertJson([
            'success'     => true,
            'is_resolved' => false,
        ]);

        $review->refresh();
        $this->assertFalse($review->is_resolved);
    }

    /**
     * Test 12: Mahasiswa dapat mengunggah dokumen revisi baru dan versi dokumen bertambah otomatis
     */
    public function test_mahasiswa_can_upload_new_revision_version(): void
    {
        Storage::fake('public');

        $proposal = Proposal::create([
            'ketua_id'  => $this->mahasiswa->id,
            'dosen_id'  => $this->dosen1->id,
            'judul_pkm' => 'Inovasi Robot Pelayan',
            'skema_pkm' => 'PKM-KC',
            'file_path' => 'proposals/robot_v1.pdf',
            'status'    => 'revisi',
        ]);

        $proposal->documents()->create([
            'version'        => 1,
            'file_path'      => 'proposals/robot_v1.pdf',
            'catatan_revisi' => 'Versi awal pengajuan.',
        ]);

        $fileRev = UploadedFile::fake()->create('robot_rev2.pdf', 800, 'application/pdf');

        $response = $this->actingAs($this->mahasiswa)
            ->post(route('mahasiswa.proposal.revision', $proposal->id), [
                'revision_file'  => $fileRev,
                'catatan_revisi' => 'Perbaikan metode kontrol motor servo.',
            ]);

        $response->assertRedirect(route('mahasiswa.dashboard'));
        $response->assertSessionHas('success');

        $proposal->refresh();
        $this->assertEquals('sedang_dibimbing', $proposal->status);

        // Verifikasi tabel proposal_documents mencatat versi 2
        $this->assertDatabaseCount('proposal_documents', 2);
        $this->assertDatabaseHas('proposal_documents', [
            'proposal_id'    => $proposal->id,
            'version'        => 2,
            'catatan_revisi' => 'Perbaikan metode kontrol motor servo.',
        ]);
    }

    /**
     * Test 13: Dosen dapat mengubah status proposal (revisi / selesai)
     */
    public function test_dosen_can_change_proposal_status(): void
    {
        $proposal = Proposal::create([
            'ketua_id'  => $this->mahasiswa->id,
            'dosen_id'  => $this->dosen1->id,
            'judul_pkm' => 'Inovasi Robot Pelayan',
            'skema_pkm' => 'PKM-KC',
            'file_path' => 'proposals/robot.pdf',
            'status'    => 'sedang_dibimbing',
        ]);

        // Ubah ke revisi
        $resRevisi = $this->actingAs($this->dosen1)
            ->patchJson(route('dosen.bimbingan.status.update', $proposal->id), [
                'status' => 'revisi',
            ]);
        $resRevisi->assertStatus(200);
        $resRevisi->assertJson([
            'success' => true,
            'status'  => 'revisi',
        ]);

        $proposal->refresh();
        $this->assertEquals('revisi', $proposal->status);

        // Ubah ke selesai
        $resSelesai = $this->actingAs($this->dosen1)
            ->patchJson(route('dosen.bimbingan.status.update', $proposal->id), [
                'status' => 'selesai',
            ]);
        $resSelesai->assertStatus(200);
        $resSelesai->assertJson([
            'success' => true,
            'status'  => 'selesai',
        ]);

        $proposal->refresh();
        $this->assertEquals('selesai', $proposal->status);
    }

    /**
     * Test 14: Dosen yang bukan pembimbing tidak berhak mengubah status proposal
     */
    public function test_unauthorized_dosen_cannot_change_proposal_status(): void
    {
        $proposal = Proposal::create([
            'ketua_id'  => $this->mahasiswa->id,
            'dosen_id'  => $this->dosen1->id,
            'judul_pkm' => 'Inovasi Robot Pelayan',
            'skema_pkm' => 'PKM-KC',
            'file_path' => 'proposals/robot.pdf',
            'status'    => 'sedang_dibimbing',
        ]);

        $response = $this->actingAs($this->dosen2)
            ->patchJson(route('dosen.bimbingan.status.update', $proposal->id), [
                'status' => 'selesai',
            ]);

        $response->assertStatus(403);

        $proposal->refresh();
        $this->assertEquals('sedang_dibimbing', $proposal->status);
    }

    /**
     * Test 15: Administrator dapat mengakses dashboard monitoring dan melihat statistik usulan
     */
    public function test_admin_can_access_dashboard_and_see_statistics(): void
    {
        // Proposal 1: Mencari Pembimbing
        Proposal::create([
            'ketua_id'  => $this->mahasiswa->id,
            'dosen_id'  => null,
            'judul_pkm' => 'Sistem IoT 1',
            'skema_pkm' => 'PKM-KC',
            'file_path' => 'proposals/p1.pdf',
            'status'    => 'diajukan',
        ]);

        // Proposal 2: Selesai
        Proposal::create([
            'ketua_id'  => $this->mahasiswa->id,
            'dosen_id'  => $this->dosen1->id,
            'judul_pkm' => 'Sistem AI 2',
            'skema_pkm' => 'PKM-RE',
            'file_path' => 'proposals/p2.pdf',
            'status'    => 'selesai',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard Monitoring Usulan PKM');
        $response->assertSee('Sistem IoT 1');
        $response->assertSee('Sistem AI 2');
    }

    /**
     * Test 16: Administrator dapat memfilter proposal berdasarkan status (misal: selesai)
     */
    public function test_admin_can_filter_proposals_by_status(): void
    {
        Proposal::create([
            'ketua_id'  => $this->mahasiswa->id,
            'dosen_id'  => null,
            'judul_pkm' => 'Usulan Belum Selesai',
            'skema_pkm' => 'PKM-KC',
            'file_path' => 'proposals/p1.pdf',
            'status'    => 'diajukan',
        ]);

        Proposal::create([
            'ketua_id'  => $this->mahasiswa->id,
            'dosen_id'  => $this->dosen1->id,
            'judul_pkm' => 'Usulan Sudah Selesai Final',
            'skema_pkm' => 'PKM-RE',
            'file_path' => 'proposals/p2.pdf',
            'status'    => 'selesai',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard', ['status' => 'selesai']));

        $response->assertStatus(200);
        $response->assertSee('Usulan Sudah Selesai Final');
        $response->assertDontSee('Usulan Belum Selesai');
    }

    /**
     * Test 17: Pengguna non-admin (mahasiswa / dosen) tidak dapat mengakses dashboard admin
     */
    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        $responseMhs = $this->actingAs($this->mahasiswa)
            ->get(route('admin.dashboard'));
        $responseMhs->assertStatus(403);

        $responseDosen = $this->actingAs($this->dosen1)
            ->get(route('admin.dashboard'));
        $responseDosen->assertStatus(403);
    }
}
