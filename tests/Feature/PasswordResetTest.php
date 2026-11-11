<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_a_reset_link_and_reset_their_password(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Lupa Password?');

        $this->get(route('login'))
            ->assertOk()
            ->assertSee(route('password.request'), false);

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
        $notification = Notification::sent($user, ResetPasswordNotification::class)->first();
        $mail = $notification->toMail($user);

        $this->assertSame('Atur Ulang Password - Raket Murah Jogja', $mail->subject);
        $this->assertStringContainsString('RAKET MURAH JOGJA', $mail->render());
        $this->assertStringContainsString('Buat Kata Sandi Baru', $mail->render());

        $this->get(route('password.reset', [
            'token' => $notification->token,
            'email' => $user->email,
        ]))
            ->assertOk()
            ->assertSee('Atur Ulang Password');

        $this->post(route('password.update'), [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }

    public function test_reset_link_request_does_not_disclose_unknown_email_addresses(): void
    {
        Notification::fake();
        $message = 'Jika email terdaftar, tautan untuk mengatur ulang kata sandi akan dikirim. Silakan periksa kotak masuk Anda.';

        $this->post(route('password.email'), ['email' => 'unknown@example.com'])
            ->assertRedirect()
            ->assertSessionHas('status', $message);

        Notification::assertNothingSent();
    }
}
