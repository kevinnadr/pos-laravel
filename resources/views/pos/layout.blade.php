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
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .sidebar-active { background-color: #eff6ff; color: #2563eb; border-right: 3px solid #2563eb; }
        .sidebar-icon-only .sidebar-text { display: none; }
        /* When collapsed, we center the icons */
        .sidebar-icon-only a { justify-content: center; padding-left: 0; padding-right: 0; }
        .sidebar-icon-only .header-title { display: none; }
        .sidebar-icon-only .user-info { display: none; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-slate-800" x-data="{ sidebarOpen: $persist(true) }">

    <!-- Sidebar -->
    <aside 
        class="bg-white border-r border-slate-200 flex flex-col z-20 shadow-sm shrink-0 transition-all duration-300 relative"
        :class="sidebarOpen ? 'w-64' : 'w-20 sidebar-icon-only'">
        
        <!-- Header -->
        <div class="h-16 flex items-center px-6 border-b border-slate-200" :class="!sidebarOpen && 'justify-center px-0'">
            <div class="flex items-center gap-2">
                @if(isset($pengaturan) && $pengaturan->logo)
                <div class="w-8 h-8 rounded-lg overflow-hidden shrink-0">
                    <img src="{{ asset('storage/' . $pengaturan->logo) }}" alt="Logo" class="w-full h-full object-cover">
                </div>
                @else
                <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center shrink-0">
                    <i data-lucide="store" class="w-5 h-5"></i>
                </div>
                @endif
                <h1 class="header-title text-base font-bold text-blue-600 tracking-tight truncate whitespace-nowrap">{{ isset($pengaturan) ? $pengaturan->nama_toko : 'Raket Murah Jogja' }}</h1>
            </div>
            
            <!-- Toggle Button (Moves depending on state) -->
            <button @click="sidebarOpen = !sidebarOpen" class="absolute right-4 text-slate-400 hover:text-blue-600 transition" :class="!sidebarOpen && 'static right-auto mt-4 mx-auto block'">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto p-3 space-y-2 mt-4">
            @if(Auth::user()->role === 'admin')
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-slate-50 transition {{ request()->routeIs('dashboard') ? 'sidebar-active' : 'text-slate-500' }}" title="Dashboard">
                <i data-lucide="layout-dashboard" class="w-5 h-5 shrink-0"></i> 
                <span class="sidebar-text truncate">Dashboard</span>
            </a>
            <a href="{{ route('kategori') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-slate-50 transition {{ request()->routeIs('kategori') ? 'sidebar-active' : 'text-slate-500' }}" title="Kategori">
                <i data-lucide="tag" class="w-5 h-5 shrink-0"></i> 
                <span class="sidebar-text truncate">Kategori</span>
            </a>
            @endif
            <a href="{{ route('produk') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-slate-50 transition {{ request()->routeIs('produk') ? 'sidebar-active' : 'text-slate-500' }}" title="Manajemen Produk">
                <i data-lucide="package" class="w-5 h-5 shrink-0"></i> 
                <span class="sidebar-text truncate">Manajemen Produk</span>
            </a>
            @if(Auth::user()->role === 'admin')
            <a href="{{ route('laporan') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-slate-50 transition {{ request()->routeIs('laporan') ? 'sidebar-active' : 'text-slate-500' }}" title="Laporan Penjualan">
                <i data-lucide="bar-chart-3" class="w-5 h-5 shrink-0"></i> 
                <span class="sidebar-text truncate">Laporan Penjualan</span>
            </a>
            <a href="{{ route('akun') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-slate-50 transition {{ request()->routeIs('akun') ? 'sidebar-active' : 'text-slate-500' }}" title="Manajemen Akun">
                <i data-lucide="users" class="w-5 h-5 shrink-0"></i> 
                <span class="sidebar-text truncate">Manajemen Akun</span>
            </a>
            @endif
            <a href="{{ route('pos') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-slate-50 transition {{ request()->routeIs('pos') ? 'sidebar-active' : 'text-slate-500' }}" title="Point of Sale">
                <i data-lucide="shopping-cart" class="w-5 h-5 shrink-0"></i> 
                <span class="sidebar-text truncate">Point of Sale</span>
            </a>
            <a href="{{ route('riwayat') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-slate-50 transition {{ request()->routeIs('riwayat') ? 'sidebar-active' : 'text-slate-500' }}" title="Riwayat Transaksi">
                <i data-lucide="history" class="w-5 h-5 shrink-0"></i> 
                <span class="sidebar-text truncate">Riwayat Transaksi</span>
            </a>
            @if(Auth::user()->role === 'admin')
            <a href="{{ route('setting') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-slate-50 transition {{ request()->routeIs('setting') ? 'sidebar-active' : 'text-slate-500' }}" title="Pengaturan">
                <i data-lucide="settings" class="w-5 h-5 shrink-0"></i> 
                <span class="sidebar-text truncate">Pengaturan</span>
            </a>
            @endif
        </nav>

        <!-- User Info & Logout (Bottom) -->
        <div class="p-4 border-t border-slate-100 flex flex-col gap-3" :class="!sidebarOpen && 'items-center px-0'">
            <div class="flex items-center gap-3" :class="!sidebarOpen && 'justify-center'">
                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold shrink-0">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="user-info">
                    <p class="text-sm font-semibold truncate w-32">{{ Auth::user()->name }}</p>
                    <span class="bg-blue-100 text-blue-600 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider">{{ Auth::user()->role }}</span>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="w-full">
                @csrf
                <button type="submit" class="flex items-center gap-2 text-sm text-slate-500 hover:text-red-600 font-medium transition w-full" :class="!sidebarOpen && 'justify-center'" title="Keluar">
                    <i data-lucide="log-out" class="w-5 h-5 shrink-0"></i> 
                    <span class="sidebar-text">Keluar</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col bg-slate-50 h-screen overflow-y-auto">
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
