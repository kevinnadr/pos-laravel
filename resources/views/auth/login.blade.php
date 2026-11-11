<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Raket Murah Jogja</title>
    @if(isset($pengaturan) && $pengaturan->logo)
    <link rel="icon" href="{{ asset('storage/' . $pengaturan->logo) }}">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f0f4f8; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <!-- Pulsing Racket Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-50 hidden flex-col items-center justify-center transition-all duration-300">
        <div class="animate-pulse flex flex-col items-center">
            <div class="w-24 h-24 bg-blue-600 rounded-full flex items-center justify-center shadow-[0_0_40px_rgba(37,99,235,0.6)] mb-6">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-white w-14 h-14 transform rotate-45">
                    <rect x="8" y="2" width="8" height="12" rx="4" />
                    <line x1="12" y1="14" x2="12" y2="18" stroke-width="1.5" />
                    <line x1="12" y1="18" x2="12" y2="23" stroke-width="3" />
                    <line x1="8" y1="6" x2="16" y2="6" stroke-width="1" opacity="0.5" />
                    <line x1="8" y1="10" x2="16" y2="10" stroke-width="1" opacity="0.5" />
                    <line x1="10" y1="2" x2="10" y2="14" stroke-width="1" opacity="0.5" />
                    <line x1="14" y1="2" x2="14" y2="14" stroke-width="1" opacity="0.5" />
                </svg>
            </div>
            <h3 class="text-white text-xl font-bold tracking-widest uppercase shadow-black drop-shadow-md">Memproses...</h3>
        </div>
    </div>
    <div class="bg-white w-full max-w-4xl rounded-2xl shadow-xl flex flex-col md:flex-row overflow-hidden min-h-[500px]">
        <!-- Left Panel -->
        <div class="w-full md:w-1/2 bg-blue-600 p-10 text-white flex flex-col justify-between">
            <div>
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-6 shadow-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-white w-7 h-7 transform rotate-45">
                        <rect x="8" y="2" width="8" height="12" rx="4" />
                        <line x1="12" y1="14" x2="12" y2="18" stroke-width="1.5" />
                        <line x1="12" y1="18" x2="12" y2="23" stroke-width="3" />
                        <line x1="8" y1="6" x2="16" y2="6" stroke-width="1" opacity="0.5" />
                        <line x1="8" y1="10" x2="16" y2="10" stroke-width="1" opacity="0.5" />
                        <line x1="10" y1="2" x2="10" y2="14" stroke-width="1" opacity="0.5" />
                        <line x1="14" y1="2" x2="14" y2="14" stroke-width="1" opacity="0.5" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold mb-4">Raket Murah Jogja</h1>
                <p class="text-blue-100 leading-relaxed">
                    Sistem kasir terpadu untuk toko peralatan badminton — raket, sepatu, tas, kok, dan aksesori.
                </p>
            </div>
            
            <div class="mt-12 space-y-3">
                <div class="flex gap-4 text-sm bg-blue-600 p-10/50 p-3 rounded-lg">
                    <span class="font-semibold w-16"></span>
                    <span></span>
                </div>
                <div class="flex gap-4 text-sm bg-blue-600 p-10/50 p-3 rounded-lg">
                    <span class="font-semibold w-16"></span>
                    <span></span>
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

                <form action="{{ route('login.post') }}" method="POST" class="space-y-5" id="loginForm">
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
                    <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:underline">Lupa Password?</a>
                </div>

                <div class="mt-12 text-center text-xs text-slate-400">
                    {{ $pengaturan->footer_login ?? '' }}
                </div>
            </div>
        </div>
    </div>
    <script>
        lucide.createIcons();
        
        document.getElementById('loginForm').addEventListener('submit', function() {
            const overlay = document.getElementById('loadingOverlay');
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
        });
    </script>
</body>
</html>
