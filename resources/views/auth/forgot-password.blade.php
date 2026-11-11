<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Raket Murah Jogja</title>
    @if(isset($pengaturan) && $pengaturan->logo)
    <link rel="icon" href="{{ asset('storage/' . $pengaturan->logo) }}">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-slate-100 p-4">
    <main class="w-full max-w-md rounded-2xl bg-white p-8 shadow-xl">
        <h1 class="text-2xl font-bold text-slate-800">Lupa Password?</h1>
        <p class="mt-2 text-sm leading-relaxed text-slate-500">
            Masukkan email yang terhubung dengan akun Anda. Kami akan mengirimkan tautan untuk mengatur ulang kata sandi.
        </p>

        @if (session('status'))
            <div class="mt-6 rounded-lg bg-green-50 p-3 text-sm text-green-700" role="status">
                {{ session('status') }}
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST" class="mt-6 space-y-5">
            @csrf
            <div>
                <label for="email" class="mb-2 block text-xs font-semibold uppercase text-slate-500">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                    autocomplete="email" placeholder="nama@email.com"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full rounded-lg bg-blue-600 py-3 font-bold text-white transition hover:bg-blue-700">
                Kirim Tautan Reset
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:underline">Kembali ke halaman masuk</a>
        </div>
    </main>
</body>
</html>
