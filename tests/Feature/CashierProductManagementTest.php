<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashierProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_only_add_stock_and_cannot_add_or_delete_products(): void
    {
        $category = Kategori::create(['nama' => 'Perlengkapan']);
        $product = $this->createProduct($category->id);

        $this->actingAs(User::factory()->create([
            'role' => 'kasir',
            'status' => 'aktif',
        ]));

        $this->get(route('produk'))
            ->assertOk()
            ->assertSee('Edit Produk')
            ->assertSee('Jumlah Stok yang Ditambahkan')
            ->assertSee('name="stok_tambah"', false)
            ->assertDontSee('name="kode"', false)
            ->assertDontSee('name="nama"', false)
            ->assertDontSee('data-lucide="plus" class="w-4 h-4"></i> Tambah Produk', false)
            ->assertDontSee('trash-2');

        $this->post(route('produk.store'), [
            'kode' => 'PRD0002',
            'nama' => 'Produk Baru',
            'kategori_id' => $category->id,
            'harga' => 10000,
            'harga_modal' => 8000,
            'stok' => 5,
            'min_stok' => 1,
        ])->assertForbidden();

        $this->delete(route('produk.destroy', $product->id))
            ->assertForbidden();

        $this->put(route('produk.update', $product->id), [
            'stok_tambah' => 3,
            'nama' => 'Nama Tidak Boleh Berubah',
            'harga' => 1,
            'stok' => 0,
        ])->assertRedirect()
            ->assertSessionHas('success', 'Stok produk berhasil ditambahkan.');

        $this->assertDatabaseHas('produks', [
            'id' => $product->id,
            'nama' => 'Produk Stok',
            'harga' => 10000,
            'stok' => 13,
        ]);
    }

    public function test_cashier_cannot_submit_a_stock_decrease_or_zero_addition(): void
    {
        $category = Kategori::create(['nama' => 'Perlengkapan']);
        $product = $this->createProduct($category->id);

        $this->actingAs(User::factory()->create([
            'role' => 'kasir',
            'status' => 'aktif',
        ]));

        $this->from(route('produk'))
            ->put(route('produk.update', $product->id), $this->productUpdateData($product, [
                'stok' => 9,
            ]))
            ->assertRedirect(route('produk'))
            ->assertSessionHasErrors('stok_tambah');

        $this->from(route('produk'))
            ->put(route('produk.update', $product->id), ['stok_tambah' => 0])
            ->assertRedirect(route('produk'))
            ->assertSessionHasErrors('stok_tambah');

        $this->assertDatabaseHas('produks', [
            'id' => $product->id,
            'stok' => 10,
        ]);
    }

    public function test_cashier_adds_an_amount_to_the_existing_stock(): void
    {
        $category = Kategori::create(['nama' => 'Perlengkapan']);
        $product = $this->createProduct($category->id);

        $this->actingAs(User::factory()->create([
            'role' => 'kasir',
            'status' => 'aktif',
        ]));

        $this->put(route('produk.update', $product->id), [
            'stok_tambah' => 8,
        ])->assertRedirect()
            ->assertSessionHas('success', 'Stok produk berhasil ditambahkan.');

        $this->assertDatabaseHas('produks', [
            'id' => $product->id,
            'stok' => 18,
        ]);
    }

    private function createProduct(int $categoryId): Produk
    {
        return Produk::create([
            'kode' => 'PRD0001',
            'nama' => 'Produk Stok',
            'kategori_id' => $categoryId,
            'harga' => 10000,
            'harga_modal' => 8000,
            'stok' => 10,
            'min_stok' => 2,
        ]);
    }

    private function productUpdateData(Produk $product, array $overrides = []): array
    {
        return array_merge([
            'kode' => $product->kode,
            'nama' => $product->nama,
            'kategori_id' => $product->kategori_id,
            'harga' => $product->harga,
            'harga_modal' => $product->harga_modal,
            'stok' => $product->stok,
            'min_stok' => $product->min_stok,
        ], $overrides);
    }
}
