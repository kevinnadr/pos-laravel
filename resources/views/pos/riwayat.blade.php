@extends('pos.layout')

@section('content')
<div class="p-6" x-data="riwayatApp()">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Riwayat Transaksi</h1>
            <p class="text-slate-500 text-sm mt-1">Daftar semua transaksi penjualan yang telah selesai</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="relative w-full md:w-80">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" x-model="search" placeholder="Cari ID transaksi / kasir..." class="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500">
            </div>
        </div>
        
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
    </div>

    <!-- Modal Transaksi Berhasil (Struk) -->
    <div x-show="showReceiptModal" class="fixed inset-0 z-[200] bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;" x-transition>
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
    body * {
        visibility: hidden;
    }
    #receiptArea, #receiptArea * {
        visibility: visible;
    }
    #receiptArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 80mm; /* Thermal printer width approx */
        margin: 0;
        padding: 10px;
        background: white;
        border: none;
    }
}
</style>

<script>
    function riwayatApp() {
        return {
            search: '',
            transaksis: @json($transaksis),
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
                }, 150);
            },

            triggerPrint() {
                window.print();
            }
        }
    }
</script>
@endsection
