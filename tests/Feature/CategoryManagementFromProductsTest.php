<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementFromProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_a_category_from_the_product_management_page(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'admin',
            'status' => 'aktif',
        ]));

        $this->from(route('produk'))
            ->post(route('kategori.store'), [
                'category_form' => '1',
                'nama' => 'Perlengkapan',
                'deskripsi' => 'Aksesori untuk olahraga',
            ])
            ->assertRedirect(route('produk'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('kategoris', [
            'nama' => 'Perlengkapan',
            'deskripsi' => 'Aksesori untuk olahraga',
        ]);

        $this->get(route('produk'))
            ->assertOk()
            ->assertSee('Tambah Kategori')
            ->assertSee('Kategori yang Tersedia')
            ->assertSee('Perlengkapan')
            ->assertSee('Aksesori untuk olahraga');
    }

    public function test_category_add_button_is_only_shown_to_admins(): void
    {
        Kategori::create(['nama' => 'Raket']);

        $this->actingAs(User::factory()->create([
            'role' => 'kasir',
            'status' => 'aktif',
        ]));

        $this->get(route('produk'))
            ->assertOk()
            ->assertDontSee('Tambah Kategori')
            ->assertDontSee('Kategori yang Tersedia');
    }

    public function test_admin_can_delete_an_unused_category_from_the_product_page(): void
    {
        $category = Kategori::create(['nama' => 'Kategori Lama']);

        $this->actingAs(User::factory()->create([
            'role' => 'admin',
            'status' => 'aktif',
        ]));

        $this->from(route('produk'))
            ->delete(route('kategori.destroy', $category->id))
            ->assertRedirect(route('produk'))
            ->assertSessionHas('success', 'Kategori berhasil dihapus.');

        $this->assertDatabaseMissing('kategoris', ['id' => $category->id]);
    }

    public function test_category_in_use_by_products_cannot_be_deleted(): void
    {
        $category = Kategori::create(['nama' => 'Raket']);
        $product = Produk::create([
            'kode' => 'PRD0001',
            'nama' => 'Raket Test',
            'kategori_id' => $category->id,
            'harga' => 100000,
            'harga_modal' => 80000,
        ]);

        $this->actingAs(User::factory()->create([
            'role' => 'admin',
            'status' => 'aktif',
        ]));

        $this->from(route('produk'))
            ->delete(route('kategori.destroy', $category->id))
            ->assertRedirect(route('produk'))
            ->assertSessionHas('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh produk.');

        $this->assertDatabaseHas('kategoris', ['id' => $category->id]);
        $this->assertDatabaseHas('produks', ['id' => $product->id]);

        $this->get(route('produk'))
            ->assertOk()
            ->assertSee('Digunakan oleh 1 produk')
            ->assertSee('Kategori masih digunakan oleh produk');
    }
}
