<x-mail::layout>
<x-slot:header>
<tr>
    <td class="header" align="center" style="padding: 28px 20px; background-color: #2563eb;">
        <a href="{{ config('app.url') }}" style="color: #ffffff; font-size: 20px; font-weight: bold; letter-spacing: 1px; text-decoration: none;">
            RAKET MURAH JOGJA
        </a>
        <p style="margin: 8px 0 0; color: #dbeafe; font-size: 12px; letter-spacing: 2px;">KEAMANAN AKUN</p>
    </td>
</tr>
</x-slot:header>

# Atur Ulang Kata Sandi

Halo {{ $userName }}, kami menerima permintaan untuk mengatur ulang kata sandi akun Anda.

Gunakan tombol di bawah ini untuk membuat kata sandi baru. Demi keamanan, tautan ini hanya dapat digunakan sekali dan akan kedaluwarsa dalam **{{ $expire }} menit**.

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin: 24px 0;">
    <tr>
        <td align="center">
            <a href="{{ $url }}" style="display: inline-block; padding: 14px 28px; border-radius: 6px; background-color: #2563eb; color: #ffffff; font-size: 15px; font-weight: bold; text-decoration: none;">
                Buat Kata Sandi Baru
            </a>
        </td>
    </tr>
</table>

Jika Anda tidak merasa meminta perubahan kata sandi, abaikan email ini. Kata sandi Anda tetap aman dan tidak akan berubah.

Salam,<br>
**Tim Raket Murah Jogja**

<x-slot:subcopy>
<x-mail::subcopy>
Jika tombol tidak dapat dibuka, salin dan tempel tautan berikut ke browser Anda: [Atur ulang kata sandi]({{ $url }})
</x-mail::subcopy>
</x-slot:subcopy>

<x-slot:footer>
<tr>
    <td align="center" style="padding: 20px; color: #64748b; font-size: 12px;">
        &copy; {{ date('Y') }} Raket Murah Jogja. Email ini dikirim otomatis, mohon tidak membalas.
    </td>
</tr>
</x-slot:footer>
</x-mail::layout>
