<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAdminManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin1;

    protected User $admin2;

    protected User $mahasiswa;

    protected User $dosen;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin1 = User::create([
            'name' => 'Admin Utama',
            'email' => 'admin1@test.com',
            'role' => 'admin',
            'nip_nim' => 'ADM-001',
            'kepakaran' => 'Biro Kemahasiswaan',
            'password' => bcrypt('password'),
        ]);

        $this->admin2 = User::create([
            'name' => 'Admin Kedua',
            'email' => 'admin2@test.com',
            'role' => 'admin',
            'nip_nim' => 'ADM-002',
            'kepakaran' => 'Bagian IT & Database',
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
            'kepakaran' => 'Artificial Intelligence',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_non_admin_cannot_access_administrator_management(): void
    {
        $this->get(route('admin.administrators.index'))
            ->assertRedirect(route('login'));

        $this->actingAs($this->mahasiswa)
            ->get(route('admin.administrators.index'))
            ->assertForbidden();

        $this->actingAs($this->dosen)
            ->get(route('admin.administrators.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_administrator_index(): void
    {
        $response = $this->actingAs($this->admin1)
            ->get(route('admin.administrators.index'));

        $response->assertOk();
        $response->assertViewIs('admin.administrators.index');
        $response->assertSee('Data Administrator Kampus');
        $response->assertSee('Admin Utama');
        $response->assertSee('Admin Kedua');
        $response->assertSee('Total: 2 Admin Terdaftar');
    }

    public function test_admin_can_search_administrators(): void
    {
        $admin3 = User::create([
            'name' => 'Admin Ketiga',
            'email' => 'admin3@test.com',
            'role' => 'admin',
            'nip_nim' => 'ADM-003',
            'kepakaran' => 'Keuangan',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($this->admin1)
            ->get(route('admin.administrators.index', ['q' => 'Kedua']));

        $response->assertOk();
        $response->assertSee('Admin Kedua');
        $response->assertSee('admin2@test.com');
        $response->assertDontSee('admin3@test.com');
    }

    public function test_admin_can_view_create_form(): void
    {
        $response = $this->actingAs($this->admin1)
            ->get(route('admin.administrators.create'));

        $response->assertOk();
        $response->assertViewIs('admin.administrators.create');
        $response->assertSee('Registrasi Administrator Baru');
    }

    public function test_admin_can_store_new_administrator(): void
    {
        $payload = [
            'name' => 'Admin Baru',
            'nip_nim' => 'ADM-003',
            'email' => 'adminbaru@test.com',
            'kepakaran' => 'Pusat Karir & PKM',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ];

        $response = $this->actingAs($this->admin1)
            ->post(route('admin.administrators.store'), $payload);

        $response->assertRedirect(route('admin.administrators.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'Admin Baru',
            'nip_nim' => 'ADM-003',
            'email' => 'adminbaru@test.com',
            'role' => 'admin',
            'kepakaran' => 'Pusat Karir & PKM',
        ]);
    }

    public function test_store_administrator_validation_errors(): void
    {
        $response = $this->actingAs($this->admin1)
            ->post(route('admin.administrators.store'), [
                'name' => '',
                'nip_nim' => 'ADM-001', // duplikat
                'email' => 'admin1@test.com', // duplikat
                'password' => '123', // kurang dari 6
                'password_confirmation' => '456',
            ]);

        $response->assertSessionHasErrors(['name', 'nip_nim', 'email', 'password']);
    }

    public function test_admin_can_view_edit_form(): void
    {
        $response = $this->actingAs($this->admin1)
            ->get(route('admin.administrators.edit', $this->admin2));

        $response->assertOk();
        $response->assertViewIs('admin.administrators.edit');
        $response->assertSee('Edit Data Administrator');
        $response->assertSee('Admin Kedua');
    }

    public function test_admin_can_update_administrator_data_and_password(): void
    {
        $payload = [
            'name' => 'Admin Kedua Updated',
            'nip_nim' => 'ADM-002-REV',
            'email' => 'admin2_updated@test.com',
            'kepakaran' => 'Kepala Biro Administrasi',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ];

        $response = $this->actingAs($this->admin1)
            ->put(route('admin.administrators.update', $this->admin2), $payload);

        $response->assertRedirect(route('admin.administrators.index'));
        $response->assertSessionHas('success');

        $this->admin2->refresh();
        $this->assertEquals('Admin Kedua Updated', $this->admin2->name);
        $this->assertEquals('ADM-002-REV', $this->admin2->nip_nim);
        $this->assertEquals('admin2_updated@test.com', $this->admin2->email);
        $this->assertTrue(Hash::check('newpassword123', $this->admin2->password));
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $response = $this->actingAs($this->admin1)
            ->delete(route('admin.administrators.destroy', $this->admin1));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->admin1->id]);
    }

    public function test_admin_can_delete_another_admin(): void
    {
        $response = $this->actingAs($this->admin1)
            ->delete(route('admin.administrators.destroy', $this->admin2));

        $response->assertRedirect(route('admin.administrators.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $this->admin2->id]);
    }

    public function test_admin_cannot_delete_last_remaining_admin(): void
    {
        // Hapus admin 2 terlebih dahulu
        $this->admin2->delete();

        // Sekarang hanya ada 1 admin
        $response = $this->actingAs($this->admin1)
            ->delete(route('admin.administrators.destroy', $this->admin1));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->admin1->id]);
    }
}
