<?php

namespace Database\Seeders;

use App\Models\Proposal;
use App\Models\ProposalMember;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Mahasiswa (Ketua Tim)
        $mahasiswa = User::firstOrCreate(
            ['email' => 'mahasiswa@example.com'],
            [
                'name'      => 'Budi Santoso',
                'role'      => 'mahasiswa',
                'nip_nim'   => '2108101001',
                'password'  => Hash::make('password'),
            ]
        );

        // 2. Akun Dosen Pembimbing 1
        $dosen1 = User::firstOrCreate(
            ['email' => 'dosen1@example.com'],
            [
                'name'      => 'Dr. Ir. Hendra Wijaya, M.Kom.',
                'role'      => 'dosen',
                'nip_nim'   => '198501012010121001',
                'kepakaran' => 'Artificial Intelligence & IoT',
                'password'  => Hash::make('password'),
            ]
        );

        // 3. Akun Dosen Pembimbing 2
        $dosen2 = User::firstOrCreate(
            ['email' => 'dosen2@example.com'],
            [
                'name'      => 'Prof. Dr. Siti Rahmawati, M.T.',
                'role'      => 'dosen',
                'nip_nim'   => '198702022012122002',
                'kepakaran' => 'Kewirausahaan & Bisnis Digital',
                'password'  => Hash::make('password'),
            ]
        );

        // 4. Akun Administrator Kampus
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'      => 'Administrator PKM',
                'role'      => 'admin',
                'nip_nim'   => '198001012005011001',
                'kepakaran' => 'Manajemen Kemahasiswaan & PKM',
                'password'  => Hash::make('password'),
            ]
        );

        // 5. Buat contoh file dummy PDF jika belum ada di storage/app/public/proposals
        $dummyPdfContent = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/MediaBox[0 0 595 842]/Parent 2 0 R/Resources<<>>>>endobj\nxref\n0 4\n0000000000 65535 f \n0000000010 00000 n \n0000000060 00000 n \n0000000117 00000 n \ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n190\n%%EOF";
        $dummyFilePath = 'proposals/sample_proposal_pkm.pdf';
        
        Storage::disk('public')->put($dummyFilePath, $dummyPdfContent);

        // 5. Buat 1 contoh proposal awal yang siap di-claim oleh dosen
        $sampleProposal = Proposal::firstOrCreate(
            ['judul_pkm' => 'Sistem Pemantauan Kualitas Air Kolam Ikan Berbasis IoT dan AI'],
            [
                'ketua_id'  => $mahasiswa->id,
                'dosen_id'  => null, // Masih mencari pembimbing
                'skema_pkm' => 'PKM-KC (Karsa Cipta)',
                'file_path' => $dummyFilePath,
                'status'    => 'diajukan',
            ]
        );

        // Anggota tim proposal contoh
        if ($sampleProposal->wasRecentlyCreated) {
            $sampleProposal->members()->createMany([
                [
                    'nama_anggota' => 'Ahmad Fauzi',
                    'nim_anggota'  => '2108101002',
                    'role_anggota' => 'Anggota 1',
                    'jobdesk'      => 'Desain sirkuit mikrokontroler sensor pH dan suhu air.',
                ],
                [
                    'nama_anggota' => 'Dewi Anggraini',
                    'nim_anggota'  => '2108101003',
                    'role_anggota' => 'Anggota 2',
                    'jobdesk'      => 'Implementasi algoritma machine learning prediksi kualitas air.',
                ],
            ]);

            $sampleProposal->documents()->firstOrCreate([
                'version' => 1,
            ], [
                'file_path'      => $dummyFilePath,
                'catatan_revisi' => 'Naskah awal usulan PKM.',
            ]);
        }
    }
}
