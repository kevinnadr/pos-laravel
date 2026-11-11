<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class StoreEmailSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_an_optional_store_contact_email(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'admin',
            'status' => 'aktif',
        ]));

        $this->put(route('setting.update'), [
            'nama_toko' => 'Toko Tes',
            'alamat_toko' => 'Jl. Tes No. 1',
            'no_telp' => '081234567890',
            'email_toko' => 'kontak@example.com',
            'footer_struk' => 'Terima kasih',
            'footer_login' => 'Hak cipta Toko Tes',
            'bank_nama' => 'BCA',
            'bank_rekening' => '1234567890',
            'bank_atas_nama' => 'Toko Tes',
        ])
            ->assertRedirect(route('setting'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('settings', [
            'email_toko' => 'kontak@example.com',
            'footer_login' => 'Hak cipta Toko Tes',
        ]);
    }

    public function test_admin_can_change_login_footer_text(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'admin',
            'status' => 'aktif',
        ]));

        $this->put(route('setting.update'), [
            'nama_toko' => 'Toko Tes',
            'alamat_toko' => 'Jl. Tes No. 1',
            'no_telp' => '081234567890',
            'footer_struk' => 'Terima kasih',
            'footer_login' => 'Hak cipta Toko Tes',
            'bank_nama' => 'BCA',
            'bank_rekening' => '1234567890',
            'bank_atas_nama' => 'Toko Tes',
        ])->assertRedirect(route('setting'));

        View::share('pengaturan', Setting::first());
        $this->post(route('logout'));

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Hak cipta Toko Tes');
    }

    public function test_store_contact_email_is_shown_on_all_receipt_screens(): void
    {
        $setting = Setting::first();
        $setting->update(['email_toko' => 'kontak@example.com']);
        View::share('pengaturan', $setting->fresh());
        $this->actingAs(User::factory()->create([
            'role' => 'admin',
            'status' => 'aktif',
        ]));

        $this->get(route('pos'))
            ->assertOk()
            ->assertSee('kontak@example.com');

        $this->get(route('riwayat'))
            ->assertOk()
            ->assertSee('kontak@example.com');

        $this->get(route('laporan'))
            ->assertOk()
            ->assertSee('kontak@example.com');
    }
}
