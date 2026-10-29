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
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            /* Latar belakang cerah dengan tekstur (mesh gradient) agar efek kaca sangat realistis */
            background-color: #f8fafc;
            background-image: 
                radial-gradient(at 0% 0%, hsla(217, 100%, 90%, 1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, hsla(213, 100%, 86%, 1) 0px, transparent 50%),
                radial-gradient(at 0% 100%, hsla(214, 97%, 87%, 1) 0px, transparent 50%),
                radial-gradient(at 100% 0%, hsla(220, 100%, 92%, 1) 0px, transparent 50%);
            background-attachment: fixed;
        }
        
        /* Sidebar Active Tab */
        .sidebar-active { 
            background-color: #1e293b; 
            color: #ffffff; 
            border-left: 4px solid #3b82f6;
            font-weight: 600;
        }

        .sidebar-icon-only .sidebar-text { display: none; }
        .sidebar-icon-only a { justify-content: center; padding-left: 0; padding-right: 0; border-left-width: 0; }
        .sidebar-icon-only .sidebar-active { border-left-width: 4px; }
        .sidebar-icon-only .header-title { display: none; }
        .sidebar-icon-only .user-info { display: none; }
        
        /* Custom scrollbar */
        .nav-scroll::-webkit-scrollbar { width: 4px; }
        .nav-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-slate-800" x-data="{ sidebarOpen: $persist(false) }">

    <!-- Sidebar (Dark Mode) -->
    <aside 
        class="flex flex-col z-20 shrink-0 relative text-white bg-slate-950 border-r border-slate-800 shadow-[4px_0_24px_rgba(0,0,0,0.05)]"
        x-init="$nextTick(() => $el.classList.add('transition-all', 'duration-300'))"
        :class="sidebarOpen ? 'w-64' : 'w-24 sidebar-icon-only'">
        
        <!-- Header -->
        <div class="h-20 flex items-center px-6 relative" :class="!sidebarOpen && 'justify-center px-0'">
            <div class="flex items-center gap-3" :class="!sidebarOpen && 'hidden'">
                @if(isset($pengaturan) && $pengaturan->logo)
                <div class="w-9 h-9 rounded-xl overflow-hidden shrink-0 shadow-sm border border-slate-700">
                    <img src="{{ asset('storage/' . $pengaturan->logo) }}" alt="Logo" class="w-full h-full object-cover">
                </div>
                @else
                <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                    <i data-lucide="store" class="w-5 h-5"></i>
                </div>
                @endif
                <h1 class="header-title text-base font-bold text-white tracking-tight truncate whitespace-nowrap">{{ isset($pengaturan) ? $pengaturan->nama_toko : 'Raket Murah Jogja' }}</h1>
            </div>
            
            <!-- Toggle Button -->
            <button @click="sidebarOpen = !sidebarOpen" class="absolute right-4 text-slate-400 hover:text-white transition-colors p-1.5 rounded-lg hover:bg-slate-800" :class="!sidebarOpen && '!static !right-auto !mx-auto block'">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto nav-scroll py-4 pl-0 pr-0 space-y-1">
            @if(Auth::user()->role === 'admin')
            <a href="{{ route('dashboard') }}" class="flex items-center gap-4 px-6 py-3.5 text-sm transition-all duration-200 {{ request()->routeIs('dashboard') ? 'sidebar-active' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}" title="Dashboard">
                <i data-lucide="layout-dashboard" class="w-5 h-5 shrink-0"></i> 
                <span class="sidebar-text truncate">Dashboard</span>
            </a>
            <a href="{{ route('kategori') }}" class="flex items-center gap-4 px-6 py-3.5 text-sm transition-all duration-200 {{ request()->routeIs('kategori') ? 'sidebar-active' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}" title="Kategori">
                <i data-lucide="tag" class="w-5 h-5 shrink-0"></i> 
                <span class="sidebar-text truncate">Kategori</span>
            </a>
            @endif
            <a href="{{ route('produk') }}" class="flex items-center gap-4 px-6 py-3.5 text-sm transition-all duration-200 {{ request()->routeIs('produk') ? 'sidebar-active' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}" title="Manajemen Produk">
                <i data-lucide="package" class="w-5 h-5 shrink-0"></i> 
                <span class="sidebar-text truncate">Manajemen Produk</span>
            </a>
            @if(Auth::user()->role === 'admin')
            <a href="{{ route('laporan') }}" class="flex items-center gap-4 px-6 py-3.5 text-sm transition-all duration-200 {{ request()->routeIs('laporan') ? 'sidebar-active' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}" title="Laporan Penjualan">
                <i data-lucide="bar-chart-3" class="w-5 h-5 shrink-0"></i> 
                <span class="sidebar-text truncate">Laporan Penjualan</span>
            </a>
            <a href="{{ route('akun') }}" class="flex items-center gap-4 px-6 py-3.5 text-sm transition-all duration-200 {{ request()->routeIs('akun') ? 'sidebar-active' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}" title="Manajemen Akun">
                <i data-lucide="users" class="w-5 h-5 shrink-0"></i> 
                <span class="sidebar-text truncate">Manajemen Akun</span>
            </a>
            @endif
            <a href="{{ route('pos') }}" class="flex items-center gap-4 px-6 py-3.5 text-sm transition-all duration-200 {{ request()->routeIs('pos') ? 'sidebar-active' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}" title="Point of Sale">
                <i data-lucide="shopping-cart" class="w-5 h-5 shrink-0"></i> 
                <span class="sidebar-text truncate">Point of Sale</span>
            </a>
            @if(Auth::user()->role === 'kasir')
            <a href="{{ route('riwayat') }}" class="flex items-center gap-4 px-6 py-3.5 text-sm transition-all duration-200 {{ request()->routeIs('riwayat') ? 'sidebar-active' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}" title="Riwayat Transaksi">
                <i data-lucide="history" class="w-5 h-5 shrink-0"></i> 
                <span class="sidebar-text truncate">Riwayat Transaksi</span>
            </a>
            @endif
            @if(Auth::user()->role === 'admin')
            <a href="{{ route('setting') }}" class="flex items-center gap-4 px-6 py-3.5 text-sm transition-all duration-200 {{ request()->routeIs('setting') ? 'sidebar-active' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}" title="Pengaturan">
                <i data-lucide="settings" class="w-5 h-5 shrink-0"></i> 
                <span class="sidebar-text truncate">Pengaturan</span>
            </a>
            @endif
        </nav>

        <!-- User Info & Logout (Bottom) -->
        <div class="p-5 mt-4 flex flex-col gap-4 border-t border-slate-800" :class="!sidebarOpen && 'items-center px-0'">
            <div class="flex items-center gap-3" :class="!sidebarOpen && 'justify-center'">
                <div class="w-10 h-10 rounded-xl bg-slate-800 text-white flex items-center justify-center font-bold shrink-0 border border-slate-700">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="user-info">
                    <p class="text-sm font-bold truncate w-32 text-white">{{ Auth::user()->name }}</p>
                    <span class="text-slate-400 text-[11px] font-semibold uppercase tracking-widest">{{ Auth::user()->role }}</span>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="w-full">
                @csrf
                <button type="submit" class="flex items-center gap-3 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/10 px-3 py-2.5 rounded-xl font-medium transition-all w-full" :class="!sidebarOpen && 'justify-center px-0'" title="Keluar">
                    <i data-lucide="log-out" class="w-5 h-5 shrink-0"></i> 
                    <span class="sidebar-text">Keluar</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col bg-slate-50 h-screen overflow-y-auto shadow-[-5px_0_25px_rgba(0,0,0,0.1)] relative z-10">
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

        @if(session('low_stock_count'))
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'warning',
                title: 'Peringatan Stok!',
                text: 'Terdapat {{ session("low_stock_count") }} produk menipis yang perlu di-restock.',
                showConfirmButton: false,
                timer: 7000,
                timerProgressBar: true,
                showCloseButton: true,
                background: '#ffffff',
                color: '#0f172a',
                iconColor: '#f59e0b',
                padding: '0.75rem 1rem',
                customClass: {
                    popup: '!rounded-2xl !shadow-[0_10px_40px_-10px_rgba(0,0,0,0.2)] !border !border-slate-100',
                    title: '!font-bold !text-sm !m-0',
                    htmlContainer: '!text-xs !text-slate-500 !font-medium !m-0 !mt-1',
                    timerProgressBar: '!bg-amber-500',
                    closeButton: '!text-slate-400 hover:!text-slate-700 hover:!bg-slate-100 !rounded-full !transition-colors'
                }
            });
        });
        @endif
    </script>

    @yield('scripts')
</body>
</html>
