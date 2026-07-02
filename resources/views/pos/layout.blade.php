<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raket Murah Jogja - POS</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Alpine Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.13.3/dist/cdn.min.js"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0ea5e9; }
        
        /* Gooey Active Tab Effect */
        .sidebar-active { 
            background-color: #f8fafc; /* Matches main content bg-slate-50 */
            color: #0ea5e9; 
            border-top-left-radius: 9999px;
            border-bottom-left-radius: 9999px;
            position: relative;
        }
        .sidebar-active::before,
        .sidebar-active::after {
            content: '';
            position: absolute;
            right: 0;
            width: 30px;
            height: 30px;
            background-color: transparent;
            z-index: 10;
        }
        .sidebar-active::before {
            top: -30px;
            border-bottom-right-radius: 30px;
            box-shadow: 15px 15px 0 15px #f8fafc;
        }
        .sidebar-active::after {
            bottom: -30px;
            border-top-right-radius: 30px;
            box-shadow: 15px -15px 0 15px #f8fafc;
        }

        .sidebar-icon-only .sidebar-text { display: none; }
        .sidebar-icon-only a { justify-content: center; padding-left: 0; padding-right: 0; }
        .sidebar-icon-only .header-title { display: none; }
        .sidebar-icon-only .user-info { display: none; }
        
        /* Custom scrollbar for dark theme */
        .nav-scroll::-webkit-scrollbar { width: 4px; }
        .nav-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-slate-800 bg-[#0ea5e9]" x-data="{ sidebarOpen: $persist(true) }">

    <!-- Sidebar -->
    <aside 
        class="flex flex-col z-20 shrink-0 transition-all duration-300 relative text-white"
        :class="sidebarOpen ? 'w-64' : 'w-24 sidebar-icon-only'">
        
        <!-- Header -->
        <div class="h-24 flex items-center px-6 relative" :class="!sidebarOpen && 'justify-center px-0'">
            <div class="flex items-center gap-3" :class="!sidebarOpen && 'hidden'">
                @if(isset($pengaturan) && $pengaturan->logo)
                <div class="w-9 h-9 rounded-xl overflow-hidden shrink-0 shadow-lg shadow-black/10">
                    <img src="{{ asset('storage/' . $pengaturan->logo) }}" alt="Logo" class="w-full h-full object-cover">
                </div>
                @else
                <div class="w-9 h-9 rounded-xl bg-sky-400 text-white flex items-center justify-center shrink-0 shadow-lg shadow-black/10">
                    <i data-lucide="store" class="w-5 h-5"></i>
                </div>
                @endif
                <h1 class="header-title text-base font-bold text-white tracking-tight truncate whitespace-nowrap">{{ isset($pengaturan) ? $pengaturan->nama_toko : 'Raket Murah Jogja' }}</h1>
            </div>
            
            <!-- Toggle Button -->
            <button @click="sidebarOpen = !sidebarOpen" class="absolute right-4 text-white/50 hover:text-white transition-colors p-1.5 rounded-lg hover:bg-white/10" :class="!sidebarOpen && '!static !right-auto !mx-auto block'">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto nav-scroll py-2 pl-4 pr-0 space-y-1">
            @if(Auth::user()->role === 'admin')
            <a href="{{ route('dashboard') }}" class="flex items-center gap-4 px-4 py-3.5 rounded-l-full text-sm font-semibold transition-all duration-200 {{ request()->routeIs('dashboard') ? 'sidebar-active shadow-[-8px_0_15px_rgba(0,0,0,0.05)]' : 'text-white/70 hover:bg-white/10 hover:text-white' }}" title="Dashboard">
                <i data-lucide="layout-dashboard" class="w-5 h-5 shrink-0"></i> 
                <span class="sidebar-text truncate">Dashboard</span>
            </a>
            <a href="{{ route('kategori') }}" class="flex items-center gap-4 px-4 py-3.5 rounded-l-full text-sm font-semibold transition-all duration-200 {{ request()->routeIs('kategori') ? 'sidebar-active shadow-[-8px_0_15px_rgba(0,0,0,0.05)]' : 'text-white/70 hover:bg-white/10 hover:text-white' }}" title="Kategori">
                <i data-lucide="tag" class="w-5 h-5 shrink-0"></i> 
                <span class="sidebar-text truncate">Kategori</span>
            </a>
            @endif
            <a href="{{ route('produk') }}" class="flex items-center gap-4 px-4 py-3.5 rounded-l-full text-sm font-semibold transition-all duration-200 {{ request()->routeIs('produk') ? 'sidebar-active shadow-[-8px_0_15px_rgba(0,0,0,0.05)]' : 'text-white/70 hover:bg-white/10 hover:text-white' }}" title="Manajemen Produk">
                <i data-lucide="package" class="w-5 h-5 shrink-0"></i> 
                <span class="sidebar-text truncate">Manajemen Produk</span>
            </a>
            @if(Auth::user()->role === 'admin')
            <a href="{{ route('laporan') }}" class="flex items-center gap-4 px-4 py-3.5 rounded-l-full text-sm font-semibold transition-all duration-200 {{ request()->routeIs('laporan') ? 'sidebar-active shadow-[-8px_0_15px_rgba(0,0,0,0.05)]' : 'text-white/70 hover:bg-white/10 hover:text-white' }}" title="Laporan Penjualan">
                <i data-lucide="bar-chart-3" class="w-5 h-5 shrink-0"></i> 
                <span class="sidebar-text truncate">Laporan Penjualan</span>
            </a>
            <a href="{{ route('akun') }}" class="flex items-center gap-4 px-4 py-3.5 rounded-l-full text-sm font-semibold transition-all duration-200 {{ request()->routeIs('akun') ? 'sidebar-active shadow-[-8px_0_15px_rgba(0,0,0,0.05)]' : 'text-white/70 hover:bg-white/10 hover:text-white' }}" title="Manajemen Akun">
                <i data-lucide="users" class="w-5 h-5 shrink-0"></i> 
                <span class="sidebar-text truncate">Manajemen Akun</span>
            </a>
            @endif
            <a href="{{ route('pos') }}" class="flex items-center gap-4 px-4 py-3.5 rounded-l-full text-sm font-semibold transition-all duration-200 {{ request()->routeIs('pos') ? 'sidebar-active shadow-[-8px_0_15px_rgba(0,0,0,0.05)]' : 'text-white/70 hover:bg-white/10 hover:text-white' }}" title="Point of Sale">
                <i data-lucide="shopping-cart" class="w-5 h-5 shrink-0"></i> 
                <span class="sidebar-text truncate">Point of Sale</span>
            </a>
            @if(Auth::user()->role === 'kasir')
            <a href="{{ route('riwayat') }}" class="flex items-center gap-4 px-4 py-3.5 rounded-l-full text-sm font-semibold transition-all duration-200 {{ request()->routeIs('riwayat') ? 'sidebar-active shadow-[-8px_0_15px_rgba(0,0,0,0.05)]' : 'text-white/70 hover:bg-white/10 hover:text-white' }}" title="Riwayat Transaksi">
                <i data-lucide="history" class="w-5 h-5 shrink-0"></i> 
                <span class="sidebar-text truncate">Riwayat Transaksi</span>
            </a>
            @endif
            @if(Auth::user()->role === 'admin')
            <a href="{{ route('setting') }}" class="flex items-center gap-4 px-4 py-3.5 rounded-l-full text-sm font-semibold transition-all duration-200 {{ request()->routeIs('setting') ? 'sidebar-active shadow-[-8px_0_15px_rgba(0,0,0,0.05)]' : 'text-white/70 hover:bg-white/10 hover:text-white' }}" title="Pengaturan">
                <i data-lucide="settings" class="w-5 h-5 shrink-0"></i> 
                <span class="sidebar-text truncate">Pengaturan</span>
            </a>
            @endif
        </nav>

        <!-- User Info & Logout (Bottom) -->
        <div class="p-5 mt-4 flex flex-col gap-4" :class="!sidebarOpen && 'items-center px-0'">
            <div class="flex items-center gap-3" :class="!sidebarOpen && 'justify-center'">
                <div class="w-10 h-10 rounded-xl bg-white/20 text-white flex items-center justify-center font-bold shrink-0 shadow-inner">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="user-info">
                    <p class="text-sm font-bold truncate w-32 text-white">{{ Auth::user()->name }}</p>
                    <span class="text-white/70 text-[11px] font-semibold uppercase tracking-widest">{{ Auth::user()->role }}</span>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="w-full">
                @csrf
                <button type="submit" class="flex items-center gap-3 text-sm text-white/70 hover:text-white hover:bg-white/10 px-3 py-2.5 rounded-xl font-semibold transition-all w-full" :class="!sidebarOpen && 'justify-center px-0'" title="Keluar">
                    <i data-lucide="log-out" class="w-5 h-5 shrink-0"></i> 
                    <span class="sidebar-text">Keluar</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col bg-slate-50 h-screen overflow-y-auto rounded-l-[2rem] shadow-[-10px_0_40px_rgba(0,0,0,0.15)] relative z-10">
        @yield('content')
    </main>

    <script>
        lucide.createIcons();
        
        // Re-initialize icons when alpine updates DOM (useful if icons disappear on toggle)
        document.addEventListener('alpine:initialized', () => {
            Alpine.effect(() => {
                setTimeout(() => lucide.createIcons(), 50);
            });
        });
    </script>
    @yield('scripts')
</body>
</html>
