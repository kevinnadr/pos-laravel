<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_warehouse_account(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'admin',
            'status' => 'aktif',
        ]));

        $this->post(route('akun.store'), [
            'nama' => 'Staf Gudang',
            'username' => 'stafgudang',
            'email' => 'gudang@gmail.com',
            'password' => 'password123',
            'role' => 'gudang',
            'status' => 'aktif',
        ])->assertRedirect()
            ->assertSessionHas('success', 'Akun berhasil ditambahkan.');

        $this->assertDatabaseHas('users', [
            'username' => 'stafgudang',
            'role' => 'gudang',
        ]);
    }

    public function test_warehouse_user_can_only_access_product_management(): void
    {
        $warehouseUser = User::factory()->create([
            'username' => 'gudanglogin',
            'role' => 'gudang',
            'status' => 'aktif',
        ]);

        $this->actingAs($warehouseUser)
            ->get(route('produk'))
            ->assertOk()
            ->assertSee('Manajemen Produk')
            ->assertSee('Tambah Produk')
            ->assertDontSee('Tambah Kategori');

        $this->get(route('dashboard'))->assertForbidden();
        $this->get(route('pos'))->assertForbidden();
        $this->get(route('riwayat'))->assertForbidden();
        $this->get(route('laporan'))->assertForbidden();
        $this->get(route('akun'))->assertForbidden();
        $this->get(route('setting'))->assertForbidden();
        $this->post(route('kategori.store'), [
            'nama' => 'Tidak Diizinkan',
        ])->assertForbidden();
    }

    public function test_warehouse_login_redirects_to_product_management(): void
    {
        $warehouseUser = User::factory()->create([
            'username' => 'gudanglogin',
            'role' => 'gudang',
            'status' => 'aktif',
        ]);

        $this->post(route('login.post'), [
            'username' => $warehouseUser->username,
            'password' => 'password',
        ])->assertRedirect(route('produk'));
    }
}
