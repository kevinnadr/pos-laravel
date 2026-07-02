<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Raket Murah Jogja</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f0f4f8; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-4xl rounded-2xl shadow-xl flex flex-col md:flex-row overflow-hidden min-h-[500px]">
        <!-- Left Panel -->
        <div class="w-full md:w-1/2 bg-blue-600 p-10 text-white flex flex-col justify-between">
            <div>
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-6">
                    <i data-lucide="shopping-cart" class="text-white w-6 h-6"></i>
                </div>
                <h1 class="text-3xl font-bold mb-4">Raket Murah Jogja</h1>
                <p class="text-blue-100 leading-relaxed">
                    Sistem kasir terpadu untuk toko peralatan badminton — raket, sepatu, tas, kok, dan aksesori.
                </p>
            </div>
            
            <div class="mt-12 space-y-3">
                <div class="flex gap-4 text-sm bg-blue-700/50 p-3 rounded-lg">
                    <span class="font-semibold w-16">Admin</span>
                    <span>admin / admin123</span>
                </div>
                <div class="flex gap-4 text-sm bg-blue-700/50 p-3 rounded-lg">
                    <span class="font-semibold w-16">Kasir</span>
                    <span>budi / kasir123</span>
                </div>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="w-full md:w-1/2 p-10 flex flex-col justify-center">
            <div class="max-w-sm w-full mx-auto">
                <h2 class="text-2xl font-bold text-slate-800 mb-8">Masuk ke Sistem</h2>

                @if(session('error'))
                <div class="bg-red-50 text-red-600 p-3 rounded-lg text-sm mb-6 flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i> {{ session('error') }}
                </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-2">Username</label>
                        <input type="text" name="username" placeholder="Masukkan username" required
                            class="w-full px-4 py-3 border border-slate-200 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-slate-50">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-2">Password</label>
                        <input type="password" name="password" placeholder="********" required
                            class="w-full px-4 py-3 border border-slate-200 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-slate-50">
                    </div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition mt-2">
                        Masuk
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <a href="#" class="text-sm text-blue-600 hover:underline">Lupa Password?</a>
                </div>

                <div class="mt-12 text-center text-xs text-slate-400">
                    &copy; 2024 Raket Murah Jogja Store. All rights reserved.
                </div>
            </div>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
