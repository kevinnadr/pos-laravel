<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Ulang Password - Raket Murah Jogja</title>
    @if(isset($pengaturan) && $pengaturan->logo)
    <link rel="icon" href="{{ asset('storage/' . $pengaturan->logo) }}">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-slate-100 p-4">
    <main class="w-full max-w-md rounded-2xl bg-white p-8 shadow-xl">
        <h1 class="text-2xl font-bold text-slate-800">Atur Ulang Password</h1>
        <p class="mt-2 text-sm leading-relaxed text-slate-500">
            Buat kata sandi baru untuk akun Anda.
        </p>

        <form action="{{ route('password.update') }}" method="POST" class="mt-6 space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label for="email" class="mb-2 block text-xs font-semibold uppercase text-slate-500">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required
                    autocomplete="email" placeholder="nama@email.com"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="mb-2 block text-xs font-semibold uppercase text-slate-500">Password Baru</label>
                <input id="password" type="password" name="password" required minlength="8"
                    autocomplete="new-password" placeholder="Minimal 8 karakter"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="mb-2 block text-xs font-semibold uppercase text-slate-500">Konfirmasi Password Baru</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8"
                    autocomplete="new-password" placeholder="Ulangi password baru"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>

            <button type="submit" class="w-full rounded-lg bg-blue-600 py-3 font-bold text-white transition hover:bg-blue-700">
                Simpan Password Baru
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:underline">Kembali ke halaman masuk</a>
        </div>
    </main>
</body>
</html>
