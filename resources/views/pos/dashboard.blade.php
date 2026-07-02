@extends('pos.layout')

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Dashboard</h1>
        <p class="text-slate-500 text-sm mt-1">Ringkasan operasional hari ini</p>
    </div>

    <!-- 4 Cards Top Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-5 rounded-2xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 flex gap-4 items-center">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-slate-500 text-xs font-semibold uppercase">Kasir Aktif</p>
                <div class="flex items-end gap-2">
                    <p class="text-2xl font-bold text-slate-800 leading-none">{{ $kasirAktif }}</p>
                </div>
                <p class="text-xs text-slate-400 mt-1">dari {{ $totalKasir }} total</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 flex gap-4 items-center">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <i data-lucide="package" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-slate-500 text-xs font-semibold uppercase">Total Produk</p>
                <div class="flex items-end gap-2">
                    <p class="text-2xl font-bold text-slate-800 leading-none">{{ $totalProduk }}</p>
                </div>
                <p class="text-xs text-slate-400 mt-1">{{ $totalUnitProduk }} unit</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 flex gap-4 items-center">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <i data-lucide="shopping-cart" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-slate-500 text-xs font-semibold uppercase">Transaksi Hari Ini</p>
                <div class="flex items-end gap-2">
                    <p class="text-2xl font-bold text-slate-800 leading-none">{{ $transaksiHariIni }}</p>
                </div>
                <p class="text-xs text-slate-400 mt-1">Rp{{ number_format($pendapatanHariIni, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 flex gap-4 items-center">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center shrink-0">
                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-slate-500 text-xs font-semibold uppercase">Stok Menipis</p>
                <div class="flex items-end gap-2">
                    <p class="text-2xl font-bold text-slate-800 leading-none">{{ $stokMenipisCount }}</p>
                </div>
                <p class="text-xs text-slate-400 mt-1">produk perlu restock</p>
            </div>
        </div>
    </div>

    <!-- Middle Row: Chart & Stok Menipis -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Chart -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100">
            <h3 class="text-sm font-bold text-slate-800 mb-6">Penjualan 7 Hari Terakhir</h3>
            <div class="h-64 w-full">
                <canvas id="dashboardChart"></canvas>
            </div>
        </div>

        <!-- Stok Menipis List -->
        <div class="bg-white p-6 rounded-2xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 flex flex-col">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-sm font-bold text-slate-800">Stok Menipis</h3>
                <span class="bg-amber-100 text-amber-700 text-xs font-bold px-2 py-1 rounded-md">{{ $stokMenipisCount }} item</span>
            </div>
            
            <div class="flex-1 overflow-y-auto space-y-4 pr-2">
                @forelse($stokMenipisList as $produk)
                <div class="flex justify-between items-center pb-4 border-b border-slate-50 last:border-0 last:pb-0">
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ $produk->nama }}</p>
                        <p class="text-xs text-slate-500">{{ $produk->kode }}</p>
                    </div>
                    <span class="bg-amber-50 text-amber-600 border border-amber-200 text-xs font-bold px-2 py-1 rounded-md">{{ $produk->stok }} sisa</span>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center h-full text-slate-400">
                    <i data-lucide="check-circle" class="w-8 h-8 mb-2 opacity-50"></i>
                    <p class="text-sm">Stok aman</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Bottom Row: Transaksi Terbaru -->
    <div class="bg-white rounded-2xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800">Transaksi Terbaru</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[600px]">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs font-bold uppercase tracking-wider">
                        <th class="p-4">ID Transaksi</th>
                        <th class="p-4">Waktu</th>
                        <th class="p-4">Kasir</th>
                        <th class="p-4">Total</th>
                        <th class="p-4">Metode</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transaksiTerbaru as $t)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="p-4 text-sm font-semibold text-blue-600">{{ $t->id }}</td>
                        <td class="p-4 text-sm text-slate-500">{{ $t->created_at->format('d/m/Y H:i') }}</td>
                        <td class="p-4 text-sm font-medium text-slate-700">{{ $t->kasir }}</td>
                        <td class="p-4 text-sm font-bold text-slate-800">Rp{{ number_format($t->total, 0, ',', '.') }}</td>
                        <td class="p-4">
                            @if($t->metode == 'cash')
                                <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold uppercase">{{ $t->metode }}</span>
                            @elseif($t->metode == 'qris')
                                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold uppercase">{{ $t->metode }}</span>
                            @else
                                <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-xs font-bold uppercase">{{ $t->metode }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-500">Belum ada transaksi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('dashboardChart').getContext('2d');
        const rawData = @json($chartData);
        const labels = Object.keys(rawData);
        const data = Object.values(rawData);

        // Create gradient for line chart
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(37, 99, 235, 0.2)'); // Blue-600 with opacity
        gradient.addColorStop(1, 'rgba(37, 99, 235, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Penjualan',
                    data: data,
                    borderColor: '#2563eb', // blue-600
                    backgroundColor: gradient,
                    borderWidth: 2,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#2563eb',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4 // Curve the line
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { 
                            borderDash: [4, 4],
                            color: '#f1f5f9'
                        },
                        ticks: {
                            callback: function(value, index, values) {
                                if (value >= 1000000) {
                                    return (value / 1000000).toFixed(1) + 'jt';
                                }
                                return value;
                            }
                        }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    });
</script>
@endsection
