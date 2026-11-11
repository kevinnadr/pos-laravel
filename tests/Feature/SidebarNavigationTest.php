<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sidebar_uses_requested_order_and_hides_point_of_sale(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'admin',
            'status' => 'aktif',
        ]));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSeeInOrder([
                'Dashboard',
                'Manajemen Produk',
                'Laporan Penjualan',
                'Manajemen Akun',
                'Pengaturan',
            ])
            ->assertDontSee('title="Kategori"', false)
            ->assertDontSee('title="Point of Sale"', false);
    }

    public function test_category_page_redirects_to_product_management(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'admin',
            'status' => 'aktif',
        ]));

        $this->get(route('kategori'))
            ->assertRedirect(route('produk'));
    }

    public function test_cashier_sidebar_keeps_point_of_sale_and_transaction_history(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'kasir',
            'status' => 'aktif',
        ]));

        $this->get(route('pos'))
            ->assertOk()
            ->assertSee('title="Point of Sale"', false)
            ->assertSee('title="Riwayat Transaksi"', false);
    }
}
