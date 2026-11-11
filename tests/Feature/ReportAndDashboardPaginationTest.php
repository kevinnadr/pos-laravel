<?php

namespace Tests\Feature;

use App\Models\Transaksi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportAndDashboardPaginationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_sales_report_keeps_period_filter_in_header_and_filters_history_by_a_single_date(): void
    {
        $this->actingAs($this->createAdmin());
        $this->createTransaction('TRX-REPORT-MATCH', '2026-11-01 09:00:00');
        $this->createTransaction('TRX-REPORT-OTHER', '2026-10-31 23:59:00');

        $this->get(route('laporan', [
            'filter' => 'semua',
            'tanggal_mode' => 'tanggal',
            'tanggal' => '2026-11-01',
        ]))
            ->assertOk()
            ->assertSee('TRX-REPORT-MATCH')
            ->assertDontSee('TRX-REPORT-OTHER')
            ->assertSee('value="10" selected', false)
            ->assertSee('Tanggal tertentu')
            ->assertSee('Rentang tanggal')
            ->assertSee('Semua tanggal');
    }

    public function test_sales_report_paginates_10_transactions_by_default_and_preserves_filters(): void
    {
        $this->actingAs($this->createAdmin());

        for ($i = 1; $i <= 16; $i++) {
            $this->createTransaction(
                sprintf('TRX-REPORT-%02d', $i),
                sprintf('2026-11-01 %02d:00:00', $i % 24)
            );
        }

        $filters = [
            'filter' => 'semua',
            'tanggal_mode' => 'rentang',
            'tanggal_mulai' => '2026-11-01',
            'tanggal_selesai' => '2026-11-01',
        ];

        $this->get(route('laporan', $filters))
            ->assertOk()
            ->assertSee('Menampilkan 1–10 dari 16 transaksi')
            ->assertSee('TRX-REPORT-16')
            ->assertDontSee('TRX-REPORT-06')
            ->assertSee('page=2');

        $this->get(route('laporan', $filters + ['page' => 2]))
            ->assertOk()
            ->assertSee('Menampilkan 11–16 dari 16 transaksi')
            ->assertSee('TRX-REPORT-01')
            ->assertDontSee('TRX-REPORT-16');
    }

    public function test_sales_report_can_select_20_transactions_per_page(): void
    {
        $this->actingAs($this->createAdmin());

        for ($i = 1; $i <= 21; $i++) {
            $this->createTransaction(
                sprintf('TRX-REPORT-SIZE-%02d', $i),
                sprintf('2026-11-01 %02d:00:00', $i % 24)
            );
        }

        $filters = ['filter' => 'semua', 'per_page' => 20];

        $this->get(route('laporan', $filters))
            ->assertOk()
            ->assertSee('Menampilkan 1–20 dari 21 transaksi')
            ->assertSee('value="20" selected', false)
            ->assertSee('page=2');

        $this->get(route('laporan', $filters + ['page' => 2]))
            ->assertOk()
            ->assertSee('Menampilkan 21–21 dari 21 transaksi');
    }

    public function test_sales_report_rejects_unsupported_page_sizes(): void
    {
        $this->actingAs($this->createAdmin());

        $this->get(route('laporan', ['per_page' => 15]))
            ->assertRedirect()
            ->assertSessionHasErrors('per_page');
    }

    public function test_admin_dashboard_paginates_transactions_15_per_page(): void
    {
        Carbon::setTestNow('2026-11-01 12:00:00');
        $this->actingAs($this->createAdmin());

        for ($i = 1; $i <= 16; $i++) {
            $this->createTransaction(
                sprintf('TRX-DASH-%02d', $i),
                sprintf('2026-11-01 %02d:00:00', $i % 24)
            );
        }
        $this->createTransaction('TRX-DASH-YESTERDAY', '2026-10-31 23:59:00');
        $this->createTransaction('TRX-DASH-TOMORROW', '2026-11-02 00:00:00');

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('TRX-DASH-16')
            ->assertDontSee('TRX-DASH-01')
            ->assertDontSee('TRX-DASH-YESTERDAY')
            ->assertDontSee('TRX-DASH-TOMORROW')
            ->assertSee('page=2');

        $this->get(route('dashboard', ['page' => 2]))
            ->assertOk()
            ->assertSee('TRX-DASH-01')
            ->assertDontSee('TRX-DASH-16');
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'status' => 'aktif',
        ]);
    }

    private function createTransaction(string $id, string $createdAt): void
    {
        $transaction = new Transaksi([
            'id' => $id,
            'kasir' => 'Kasir Tes',
            'subtotal' => 1000,
            'diskon' => 0,
            'total' => 1000,
            'metode' => 'cash',
            'bayar' => 1000,
        ]);
        $transaction->created_at = $createdAt;
        $transaction->save();
    }
}
