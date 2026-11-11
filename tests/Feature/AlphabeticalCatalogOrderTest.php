<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlphabeticalCatalogOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_low_stock_item_opens_its_product_edit_form(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'admin',
            'status' => 'aktif',
        ]));

        $category = Kategori::create(['nama' => 'Raket']);
        $product = Produk::create([
            'kode' => 'RKT-LOW',
            'nama' => 'Raket Stok Menipis',
            'kategori_id' => $category->id,
            'harga' => 1000,
            'harga_modal' => 500,
            'stok' => 0,
            'min_stok' => 2,
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('produk', ['edit' => $product->id]), false);

        $this->get(route('produk', ['edit' => $product->id]))
            ->assertOk()
            ->assertSee('Raket Stok Menipis')
            ->assertSee("const editId = '".$product->id."';", false)
            ->assertSee('this.$nextTick(() => this.openModal(produk));', false);
    }

    public function test_categories_are_alphabetical_in_product_form_and_pos_filters(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'admin',
            'status' => 'aktif',
        ]));

        $categoryIds = [];
        foreach (['Tas', 'Raket', 'Aksesori', 'Shuttlecock'] as $name) {
            $categoryIds[$name] = Kategori::create(['nama' => $name])->id;
        }

        $expectedOrder = ['Aksesori', 'Raket', 'Shuttlecock', 'Tas'];
        $this->createProduct('Zebra', $categoryIds['Raket']);
        $this->createProduct('Adidas', $categoryIds['Raket']);

        $this->get(route('produk'))
            ->assertOk()
            ->assertSeeInOrder($expectedOrder)
            ->assertSeeInOrder(['Adidas', 'Zebra']);

        $this->get(route('pos'))
            ->assertOk()
            ->assertSeeInOrder($expectedOrder)
            ->assertSeeInOrder(['Adidas', 'Zebra']);

    }

    private function createProduct(string $name, int $categoryId): void
    {
        Produk::create([
            'kode' => strtoupper(substr($name, 0, 3)),
            'nama' => $name,
            'kategori_id' => $categoryId,
            'harga' => 1000,
            'harga_modal' => 500,
            'stok' => 2,
            'min_stok' => 1,
        ]);
    }
}
