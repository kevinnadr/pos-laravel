<?php

namespace Tests\Feature;

use App\Models\Transaksi;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionHistoryFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_history_can_be_filtered_by_a_date_range(): void
    {
        $this->actingAs(User::factory()->create());

        $this->createTransaction('TRX-BEFORE', '2026-10-30 23:59:00');
        $this->createTransaction('TRX-START', '2026-10-31 00:00:00');
        $this->createTransaction('TRX-END', '2026-11-01 23:59:00');
        $this->createTransaction('TRX-AFTER', '2026-11-02 00:00:00');

        $this->get(route('riwayat', [
            'tanggal_mulai' => '2026-10-31',
            'tanggal_selesai' => '2026-11-01',
        ]))
            ->assertOk()
            ->assertSee('TRX-START')
            ->assertSee('TRX-END')
            ->assertDontSee('TRX-BEFORE')
            ->assertDontSee('TRX-AFTER');
    }

    public function test_transaction_history_can_be_filtered_by_one_exact_date(): void
    {
        $this->actingAs(User::factory()->create());

        $this->createTransaction('TRX-PREVIOUS', '2026-10-31 23:59:00');
        $this->createTransaction('TRX-MORNING', '2026-11-01 08:15:00');
        $this->createTransaction('TRX-EVENING', '2026-11-01 21:45:00');
        $this->createTransaction('TRX-NEXT', '2026-11-02 00:00:00');

        $this->get(route('riwayat', [
            'mode_tanggal' => 'tanggal',
            'tanggal' => '2026-11-01',
        ]))
            ->assertOk()
            ->assertSee('TRX-MORNING')
            ->assertSee('TRX-EVENING')
            ->assertDontSee('TRX-PREVIOUS')
            ->assertDontSee('TRX-NEXT');
    }

    public function test_transaction_history_rejects_an_end_date_before_the_start_date(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('riwayat', [
            'tanggal_mulai' => '2026-11-02',
            'tanggal_selesai' => '2026-11-01',
        ]))
            ->assertRedirect()
            ->assertSessionHasErrors('tanggal_selesai');
    }

    public function test_checkout_records_the_logged_in_cashier_name(): void
    {
        $cashier = User::factory()->create([
            'name' => 'Kasir Bernama',
            'role' => 'kasir',
            'status' => 'aktif',
        ]);
        $this->actingAs($cashier);

        $category = Kategori::create(['nama' => 'Raket']);
        $product = Produk::create([
            'kode' => 'RKT-TEST',
            'nama' => 'Raket Tes',
            'kategori_id' => $category->id,
            'harga' => 10000,
            'harga_modal' => 7000,
            'stok' => 5,
            'min_stok' => 1,
        ]);

        $response = $this->postJson(route('pos.checkout'), [
            'subtotal' => 10000,
            'diskon' => 0,
            'total' => 10000,
            'metode' => 'cash',
            'bayar' => 12000,
            'items' => [
                ['id' => $product->id, 'qty' => 1],
            ],
        ]);

        $response->assertOk()->assertJson([
            'success' => true,
            'kasir' => 'Kasir Bernama',
            'kembalian' => 2000,
        ]);
        $this->assertDatabaseHas('transaksis', [
            'user_id' => $cashier->id,
            'kasir' => 'Kasir Bernama',
        ]);
    }

    private function createTransaction(string $id, string $createdAt): void
    {
        $transaction = new Transaksi([
            'id' => $id,
            'kasir' => 'Kasir Tes',
            'subtotal' => 10000,
            'diskon' => 0,
            'total' => 10000,
            'metode' => 'cash',
            'bayar' => 10000,
        ]);
        $transaction->created_at = $createdAt;
        $transaction->save();
    }
}
