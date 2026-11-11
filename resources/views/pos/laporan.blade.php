@extends('pos.layout')

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('content')
<div class="p-6" x-data="laporanApp()">
    <!-- Header section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Laporan Penjualan</h1>
            <p class="text-slate-500 text-sm mt-1">
                Berisi data penjualan dan keuangan toko. Gunakan filter untuk menyesuaikan periode laporan.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <form id="filterForm" action="{{ route('laporan') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <select name="filter" x-model="filterMode" class="bg-white border border-slate-200 text-slate-700 px-3 py-2 rounded-lg text-sm focus:outline-none focus:border-blue-500">
                    @foreach ($filterOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    Terapkan
                </button>
            </form>

            <a href="{{ route('laporan.export.penjualan', $exportFilters) }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition shadow-sm">
                <i data-lucide="file-text" class="w-4 h-4"></i> Export Penjualan
            </a>
            <a href="{{ route('laporan.export.keuangan', $exportFilters) }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition shadow-sm">
                <i data-lucide="download" class="w-4 h-4"></i> Export Keuangan
            </a>
        </div>
    </div>

    <!-- 4 Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
            <p class="text-slate-500 text-xs font-semibold mb-2">Total Penjualan</p>
            <p class="text-2xl font-bold text-slate-800">Rp{{ number_format($totalPenjualan, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ $jumlahTransaksi }} transaksi</p>
        </div>
        
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
            <p class="text-slate-500 text-xs font-semibold mb-2">Total Modal (HPP)</p>
            <p class="text-2xl font-bold text-amber-500">Rp{{ number_format($totalModal, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1">Harga pokok penjualan</p>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
            <p class="text-slate-500 text-xs font-semibold mb-2">Laba Kotor</p>
            <p class="text-2xl font-bold text-emerald-500">Rp{{ number_format($labaKotor, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1">Penjualan - Modal</p>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
            <p class="text-slate-500 text-xs font-semibold mb-2">Margin Laba</p>
            <p class="text-2xl font-bold text-blue-500">{{ number_format($marginLaba, 1) }}%</p>
            <p class="text-xs text-slate-400 mt-1">Dari total penjualan</p>
        </div>
    </div>

    <!-- Bar Chart -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 mb-6">
        <h3 class="text-sm font-bold text-slate-800 mb-6">Grafik Penjualan {{ ucfirst($filter) }}</h3>
        <div class="h-64 w-full">
            <canvas id="laporanChart"></canvas>
        </div>
    </div>

    <!-- Filter & Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <form action="{{ route('laporan') }}" method="GET" class="p-4 border-b border-slate-100 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <input type="hidden" name="filter" value="{{ $filter }}">
            <div class="flex w-full flex-col gap-2 sm:flex-row lg:w-auto">
                <div class="relative w-full sm:w-72">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="search" name="search" x-model="search" value="{{ $search }}" placeholder="Cari ID transaksi / kasir..." class="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500">
                </div>
                <select name="tanggal_mode" x-model="dateMode" aria-label="Filter tanggal riwayat penjualan"
                    class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-500 focus:outline-none">
                    <option value="">Semua tanggal</option>
                    <option value="tanggal">Tanggal tertentu</option>
                    <option value="rentang">Rentang tanggal</option>
                </select>
            </div>
            <div class="flex w-full flex-col gap-2 sm:flex-row lg:w-auto">
                <input type="date" name="tanggal" value="{{ $tanggal }}"
                    x-show="dateMode === 'tanggal'" :disabled="dateMode !== 'tanggal'" :required="dateMode === 'tanggal'"
                    aria-label="Pilih tanggal riwayat penjualan"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-blue-500 focus:outline-none sm:w-auto">
                <div x-show="dateMode === 'rentang'" class="flex w-full flex-col gap-2 sm:flex-row sm:w-auto">
                    <input type="date" name="tanggal_mulai" value="{{ $tanggalMulai }}"
                        :disabled="dateMode !== 'rentang'" :required="dateMode === 'rentang'"
                        aria-label="Tanggal mulai riwayat penjualan"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-blue-500 focus:outline-none sm:w-auto">
                    <input type="date" name="tanggal_selesai" value="{{ $tanggalSelesai }}"
                        :disabled="dateMode !== 'rentang'" :required="dateMode === 'rentang'"
                        aria-label="Tanggal selesai riwayat penjualan"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-blue-500 focus:outline-none sm:w-auto">
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    Per halaman
                    <select name="per_page" aria-label="Jumlah transaksi per halaman" onchange="this.form.submit()"
                        class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-500 focus:outline-none">
                        @foreach ([10, 20, 50, 100] as $option)
                            <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </label>
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">Terapkan</button>
                @if ($tanggalMode || $search)
                    <a href="{{ route('laporan', ['filter' => $filter, 'per_page' => $perPage]) }}" class="rounded-lg border border-slate-200 px-4 py-2 text-center text-sm font-medium text-slate-600 hover:bg-slate-50">Reset</a>
                @endif
            </div>
            @error('tanggal')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
            @error('tanggal_mulai')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
            @error('tanggal_selesai')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </form>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-xs font-semibold tracking-wider">
                        <th class="p-4">ID Transaksi</th>
                        <th class="p-4">Waktu</th>
                        <th class="p-4">Kasir</th>
                        <th class="p-4 text-center">Item</th>
                        <th class="p-4">Total</th>
                        <th class="p-4 text-center">Metode</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="t in filteredTransaksis" :key="t.id">
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="p-4 text-sm font-semibold text-blue-600" x-text="t.id"></td>
                            <td class="p-4 text-sm text-slate-500" x-text="formatDate(t.created_at)"></td>
                            <td class="p-4 text-sm font-medium text-slate-700" x-text="t.kasir"></td>
                            <td class="p-4 text-sm text-slate-500 text-center" x-text="t.items.length + ' item'"></td>
                            <td class="p-4 text-sm font-bold text-slate-800" x-text="formatRupiah(t.total)"></td>
                            <td class="p-4 text-center">
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                    :class="{
                                        'bg-emerald-100 text-emerald-700': t.metode === 'cash',
                                        'bg-blue-100 text-blue-700': t.metode === 'qris',
                                        'bg-purple-100 text-purple-700': t.metode === 'transfer'
                                    }" x-text="t.metode"></span>
                            </td>
                            <td class="p-4 flex justify-center gap-2">
                                <button @click="viewReceipt(t)" class="text-slate-500 hover:text-blue-600 px-3 py-1.5 border border-slate-200 rounded-md text-xs font-medium hover:bg-slate-50 flex items-center gap-1 transition" title="Lihat Struk">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i> Lihat
                                </button>
                                <button @click="printReceipt(t)" class="text-slate-500 hover:text-blue-600 px-3 py-1.5 border border-slate-200 rounded-md text-xs font-medium hover:bg-slate-50 flex items-center gap-1 transition" title="Cetak">
                                    <i data-lucide="printer" class="w-3.5 h-3.5"></i> Cetak
                                </button>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filteredTransaksis.length === 0">
                        <td colspan="7" class="p-8 text-center text-slate-500">Tidak ada transaksi ditemukan.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @if ($transaksis->hasPages())
            <div class="flex flex-col gap-3 border-t border-slate-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-500">
                    Menampilkan {{ $transaksis->firstItem() }}–{{ $transaksis->lastItem() }} dari {{ $transaksis->total() }} transaksi
                </p>
                {{ $transaksis->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Transaksi Berhasil (Struk) -->
    <div id="receiptModal" x-show="showReceiptModal" class="fixed inset-0 z-[200] bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;" x-transition>
        <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden relative">
            <button @click="closeReceipt()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 transition z-10 bg-white/80 rounded-full p-1"><i data-lucide="x" class="w-5 h-5"></i></button>
            
            <div class="pt-10 pb-6 px-6 flex flex-col items-center border-b border-dashed border-slate-300 bg-white relative">
                <div class="w-16 h-16 bg-blue-100 text-blue-500 rounded-full flex items-center justify-center mb-4">
                    <i data-lucide="receipt" class="w-8 h-8 stroke-[3]"></i>
                </div>
                <h2 class="font-bold text-xl text-slate-800">Detail Transaksi</h2>
                <p class="text-slate-400 text-sm mt-1 uppercase tracking-wider" x-text="selectedTrx?.id"></p>
            </div>

            <!-- Area Struk -->
            <div class="px-6 pt-6 pb-4 bg-slate-50 flex-1 overflow-y-auto custom-scrollbar border border-slate-200 m-6 rounded-xl relative" id="receiptArea">
                <div class="text-center mb-6">
                    @if(isset($pengaturan) && $pengaturan->logo)
                    <img src="{{ asset('storage/' . $pengaturan->logo) }}" alt="Logo" class="w-12 h-12 mx-auto mb-2 object-cover rounded">
                    @endif
                    <h3 class="font-black text-slate-800 tracking-widest uppercase text-base">{{ isset($pengaturan) ? $pengaturan->nama_toko : 'RAKET MURAH JOGJA' }}</h3>
                    <p class="text-xs text-slate-500 mt-1">{{ isset($pengaturan) ? $pengaturan->alamat_toko : 'Jl. Olahraga No. 88, Bandung' }}</p>
                    @if(isset($pengaturan) && $pengaturan->email_toko)
                    <p class="text-xs text-slate-500">{{ $pengaturan->email_toko }}</p>
                    @endif
                    <p class="text-xs text-slate-500">{{ isset($pengaturan) ? $pengaturan->no_telp : '081234567890' }}</p>
                    <div class="w-full border-t border-dashed border-slate-300 mt-4"></div>
                </div>

                <div class="flex justify-between text-xs text-slate-500 font-mono mb-4">
                    <div class="flex flex-col gap-1">
                        <span>Tgl:</span>
                        <span>Kasir:</span>
                    </div>
                    <div class="flex flex-col gap-1 text-right text-slate-800 font-bold">
                        <span x-text="selectedTrx ? formatDate(selectedTrx.created_at) : ''"></span>
                        <span x-text="selectedTrx?.kasir"></span>
                    </div>
                </div>

                <div class="space-y-4 font-mono text-xs">
                    <template x-for="item in (selectedTrx ? selectedTrx.items : [])" :key="item.id">
                        <div>
                            <div class="text-slate-800 font-bold truncate" x-text="item.nama_produk"></div>
                            <div class="flex justify-between text-slate-500 mt-1">
                                <span x-text="`${item.qty} x ${formatRupiah(item.harga)}`"></span>
                                <span class="font-bold text-slate-800" x-text="formatRupiah(item.qty * item.harga)"></span>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="w-full border-t border-dashed border-slate-300 my-4"></div>

                <div class="space-y-2 font-mono text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal</span>
                        <span x-text="selectedTrx ? formatRupiah(selectedTrx.subtotal) : ''"></span>
                    </div>
                    <div x-show="selectedTrx && selectedTrx.diskon > 0" class="flex justify-between text-red-500 font-bold">
                        <span>Diskon</span>
                        <span x-text="selectedTrx ? '-' + formatRupiah(selectedTrx.diskon) : ''"></span>
                    </div>
                    <div class="flex justify-between font-black text-base text-slate-800 mt-2 pt-2 border-t border-slate-200">
                        <span>TOTAL BAYAR</span>
                        <span x-text="selectedTrx ? formatRupiah(selectedTrx.total) : ''"></span>
                    </div>
                    
                    <div class="flex justify-between text-slate-500 mt-4">
                        <span>Metode</span>
                        <span class="uppercase font-bold" x-text="selectedTrx?.metode"></span>
                    </div>

                    <!-- Rincian Cash jika metode cash -->
                    <template x-if="selectedTrx?.metode === 'cash'">
                        <div>
                            <div class="flex justify-between text-slate-500 mt-1">
                                <span>Tunai</span>
                                <span x-text="formatRupiah(selectedTrx.bayar)"></span>
                            </div>
                            <div class="flex justify-between text-slate-500 mt-1">
                                <span>Kembali</span>
                                <span x-text="formatRupiah(selectedTrx.kembalian)"></span>
                            </div>
                        </div>
                    </template>
                </div>
                
                <div class="w-full border-t border-dashed border-slate-300 mt-6 mb-4"></div>
                <p class="text-center text-[10px] text-slate-400 font-mono">{{ isset($pengaturan) ? $pengaturan->footer_struk : 'Terima kasih atas kunjungan Anda!' }}</p>
            </div>

            <!-- Footer Modal Berhasil -->
            <div class="p-4 bg-white flex gap-3 shadow-[0_-10px_20px_-15px_rgba(0,0,0,0.05)] z-10 border-t border-slate-100">
                <button @click="closeReceipt()" class="flex-1 px-4 py-3 border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 rounded-xl font-bold transition text-sm">
                    Tutup
                </button>
                <button @click="triggerPrint()" class="flex-1 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg shadow-blue-600/30 transition flex items-center justify-center gap-2 text-sm">
                    <i data-lucide="printer" class="w-4 h-4"></i> Cetak Struk
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Sembunyikan scrollbar tapi tetap bisa scroll */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent; 
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1; 
    border-radius: 10px;
}
.custom-scrollbar:hover::-webkit-scrollbar-thumb {
    background: #94a3b8; 
}

/* Print Styles */
@media print {
    @page {
        margin: 0;
    }

    body * {
        visibility: hidden !important;
    }

    html, body {
        display: block !important;
        width: auto !important;
        height: auto !important;
        min-height: 0 !important;
        overflow: visible !important;
        position: static !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    #receiptModal {
        display: block !important;
        position: static !important;
        width: auto !important;
        height: auto !important;
        overflow: visible !important;
        padding: 0 !important;
        background: transparent !important;
        backdrop-filter: none !important;
        transform: none !important;
        visibility: visible !important;
    }

    #receiptModal > div {
        display: block !important;
        position: static !important;
        width: auto !important;
        height: auto !important;
        max-height: none !important;
        overflow: visible !important;
        transform: none !important;
        margin: 0 !important;
        border: none !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }

    #receiptModal > div > :not(#receiptArea) {
        display: none !important;
    }

    #receiptArea, #receiptArea * {
        visibility: visible !important;
    }

    #receiptArea {
        display: block !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 80mm !important;
        box-sizing: border-box !important;
        height: auto !important;
        max-height: none !important;
        overflow: visible !important;
        margin: 0 !important;
        padding: 4mm !important;
        background: white !important;
        border: none !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }
}
</style>

<script>
    function laporanApp() {
        return {
            search: @js($search),
            filterMode: @js($filter),
            dateMode: @js($tanggalMode),
            transaksis: @json($transaksiHalaman),
            showReceiptModal: false,
            selectedTrx: null,
            
            get filteredTransaksis() {
                if (this.search === '') return this.transaksis;
                const searchLower = this.search.toLowerCase();
                return this.transaksis.filter(t => 
                    t.id.toString().toLowerCase().includes(searchLower) ||
                    t.kasir.toLowerCase().includes(searchLower)
                );
            },
            
            formatRupiah(number) {
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(number);
            },
            
            formatDate(dateString) {
                const d = new Date(dateString);
                const day = d.getDate().toString().padStart(2, '0');
                const month = (d.getMonth() + 1).toString().padStart(2, '0');
                const year = d.getFullYear();
                const hours = d.getHours().toString().padStart(2, '0');
                const minutes = d.getMinutes().toString().padStart(2, '0');
                return `${day}/${month}/${year} ${hours}:${minutes}`;
            },

            viewReceipt(trx) {
                this.selectedTrx = trx;
                this.showReceiptModal = true;
                setTimeout(() => lucide.createIcons(), 50);
            },

            closeReceipt() {
                this.showReceiptModal = false;
                this.selectedTrx = null;
            },

            printReceipt(trx) {
                this.selectedTrx = trx;
                this.showReceiptModal = true;
                setTimeout(() => {
                    lucide.createIcons();
                    window.print();
                }, 150); // delay to let Alpine render before print
            },

            triggerPrint() {
                window.print();
            },

            init() {
                // Initialize Chart
                const ctx = document.getElementById('laporanChart').getContext('2d');
                const rawData = @json($chartData);
                const labels = Object.keys(rawData);
                const data = Object.values(rawData);

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Penjualan',
                            data: data,
                            backgroundColor: '#3b82f6', // blue-500
                            borderRadius: 4,
                            barPercentage: 0.5,
                            maxBarThickness: 40
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
                                            label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(context.parsed.y);
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
            }
        }
    }
</script>
@endsection
