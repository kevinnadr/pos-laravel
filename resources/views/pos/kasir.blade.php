@extends('pos.layout')

@section('content')
<div class="flex-1 flex relative h-[calc(100vh-theme(spacing.16))] overflow-hidden" x-data="posApp()">
    <!-- Bagian Produk (Kiri) -->
    <div class="flex-1 flex flex-col h-full bg-slate-50">
        <!-- Header & Pencarian -->
        <div class="p-4 bg-white shadow-sm flex flex-col gap-4 z-10 relative border-b border-slate-200">
            <div class="flex gap-3">
                <div class="relative flex-1">
                    <i data-lucide="search" class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" x-model="search" placeholder="Cari produk..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
            </div>

            <!-- Filter Kategori Pill -->
            <div class="flex gap-2 overflow-x-auto whitespace-nowrap custom-scrollbar pb-1">
                <button @click="kategoriFilter = 0" :class="{'bg-blue-600 text-white': kategoriFilter === 0, 'bg-slate-100 text-slate-600 hover:bg-slate-200': kategoriFilter !== 0}" class="px-5 py-1.5 rounded-full text-sm font-semibold transition">Semua</button>
                @foreach($kategoris as $k)
                <button @click="kategoriFilter = {{ $k->id }}" :class="{'bg-blue-600 text-white': kategoriFilter === {{ $k->id }}, 'bg-slate-100 text-slate-600 hover:bg-slate-200': kategoriFilter !== {{ $k->id }}}" class="px-5 py-1.5 rounded-full text-sm font-semibold transition">{{ $k->nama }}</button>
                @endforeach
            </div>
        </div>

        <!-- Grid Produk -->
        <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                <template x-for="p in filteredProduks" :key="p.id">
                    <div @click="addToCart(p)" class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:border-blue-500 hover:shadow-md transition cursor-pointer flex flex-col group overflow-hidden relative">
                        
                        <!-- Badge Menipis -->
                        <template x-if="p.stok <= p.min_stok">
                            <div class="absolute top-2 left-2 z-10 bg-amber-100 text-amber-700 text-[10px] font-bold px-2 py-1 rounded flex items-center gap-1 shadow-sm border border-amber-200">
                                <i data-lucide="alert-triangle" class="w-3 h-3"></i> Menipis
                            </div>
                        </template>

                        <!-- Badge Qty di Keranjang -->
                        <template x-if="getCartQty(p.id) > 0">
                            <div class="absolute top-2 right-2 z-10 bg-blue-600 text-white text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center shadow-md shadow-blue-600/30" x-text="getCartQty(p.id)">
                            </div>
                        </template>

                        <!-- Cover Image -->
                        <div class="aspect-[4/3] bg-slate-100 w-full overflow-hidden relative">
                            <template x-if="p.foto">
                                <img :src="`/storage/${p.foto}`" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            </template>
                            <template x-if="!p.foto">
                                <div class="w-full h-full flex items-center justify-center text-slate-300 group-hover:scale-105 transition duration-300">
                                    <i data-lucide="package" class="w-12 h-12"></i>
                                </div>
                            </template>
                        </div>

                        <!-- Info Produk -->
                        <div class="p-3 flex flex-col flex-1">
                            <h3 class="text-sm font-bold text-slate-800 leading-tight mb-1 line-clamp-2" x-text="p.nama"></h3>
                            <div class="text-blue-600 font-bold mt-auto" x-text="formatRp(p.harga)"></div>
                            <div class="text-xs text-slate-500 mt-1 font-medium" x-text="'Stok: ' + p.stok"></div>
                        </div>
                    </div>
                </template>
                <div x-show="filteredProduks.length === 0" class="col-span-full py-12 text-center text-slate-400">
                    Tidak ada produk ditemukan.
                </div>
            </div>
        </div>
    </div>

    <!-- Bagian Keranjang (Kanan) -->
    <div class="w-96 bg-white flex flex-col h-full z-20 shadow-[-10px_0_30px_rgba(0,0,0,0.05)] shrink-0 border-l border-slate-200">
        <!-- Header Keranjang -->
        <div class="px-5 py-4 border-b border-blue-500 bg-blue-600 text-white flex items-center justify-between">
            <h2 class="font-bold flex items-center gap-2">
                <i data-lucide="shopping-cart" class="w-5 h-5"></i> 
                Keranjang <span class="bg-blue-500 w-6 h-6 flex items-center justify-center rounded-full text-xs font-bold" x-text="cartItemCount"></span>
            </h2>
            <button @click="cart = []" x-show="cart.length > 0" class="text-blue-200 hover:text-white text-xs font-semibold transition">Kosongkan</button>
        </div>

        <!-- Isi Keranjang -->
        <div class="flex-1 overflow-y-auto p-4 bg-slate-50 custom-scrollbar">
            <div x-show="cart.length === 0" class="h-full flex flex-col items-center justify-center text-slate-400 space-y-3">
                <i data-lucide="shopping-cart" class="w-16 h-16 opacity-20"></i>
                <p class="font-medium text-sm text-slate-500">Belum ada pesanan</p>
            </div>

            <div class="space-y-3">
                <template x-for="item in cart" :key="item.id">
                    <div class="bg-white p-3 rounded-xl shadow-sm border border-slate-100 flex items-center gap-3">
                        <div class="w-12 h-12 bg-slate-100 rounded-lg overflow-hidden shrink-0 border border-slate-200">
                            <template x-if="item.foto">
                                <img :src="`/storage/${item.foto}`" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!item.foto">
                                <div class="w-full h-full flex items-center justify-center text-slate-300">
                                    <i data-lucide="package" class="w-5 h-5"></i>
                                </div>
                            </template>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-bold text-slate-800 line-clamp-2 leading-tight" x-text="item.nama"></h4>
                            <p class="text-blue-600 font-bold text-xs mt-1" x-text="formatRp(item.harga)"></p>
                        </div>
                        <div class="flex items-center gap-1 bg-slate-50 p-1 rounded-lg border border-slate-200">
                            <button @click="updateQty(item.id, -1)" class="w-7 h-7 bg-white rounded flex items-center justify-center shadow-sm text-slate-600 hover:text-blue-600 hover:bg-slate-50 font-bold">-</button>
                            <span class="w-6 text-center text-sm font-bold text-slate-700" x-text="item.qty"></span>
                            <button @click="updateQty(item.id, 1)" class="w-7 h-7 bg-white rounded flex items-center justify-center shadow-sm text-slate-600 hover:text-blue-600 hover:bg-slate-50 font-bold">+</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Footer Checkout -->
        <div class="p-5 border-t border-slate-200 bg-white shadow-[0_-10px_20px_-15px_rgba(0,0,0,0.1)]">
            <div class="flex justify-between items-center mb-4">
                <span class="text-slate-500 font-medium text-sm" x-text="`Total (${cartItemCount} item)`"></span>
                <span class="font-bold text-2xl text-slate-800" x-text="formatRp(subtotal)"></span>
            </div>
            <button @click="openPaymentModal()" :disabled="cart.length === 0" class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white py-3.5 rounded-xl font-bold flex items-center justify-center gap-2 shadow-lg shadow-blue-600/30 transition">
                <i data-lucide="credit-card" class="w-5 h-5"></i> Bayar
            </button>
        </div>
    </div>

    <!-- Modal Pembayaran -->
    <div x-show="showPaymentModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;" x-transition>
        <div @click.away="closePaymentModal()" class="bg-white w-full max-w-md rounded-2xl shadow-2xl flex flex-col max-h-[95vh]">
            <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-white rounded-t-2xl">
                <h3 class="font-bold text-lg text-slate-800">Pembayaran</h3>
                <button @click="closePaymentModal()" class="text-slate-400 hover:bg-slate-100 w-8 h-8 rounded-full flex items-center justify-center transition"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            
            <div class="p-5 flex-1 overflow-y-auto space-y-5 custom-scrollbar">
                
                <!-- Tab Metode Pembayaran -->
                <div class="grid grid-cols-3 gap-2 p-1 bg-slate-100 rounded-xl">
                    <button @click="metode = 'cash'; updateBayarDefault();" :class="metode === 'cash' ? 'bg-white text-blue-600 shadow-sm border border-slate-200' : 'text-slate-500 hover:text-slate-700'" class="py-2.5 rounded-lg text-sm font-bold flex flex-col items-center justify-center gap-1 transition">
                        <i data-lucide="banknote" class="w-5 h-5"></i> Cash
                    </button>
                    <button @click="metode = 'qris'; bayar = totalAkhir;" :class="metode === 'qris' ? 'bg-white text-blue-600 shadow-sm border border-slate-200' : 'text-slate-500 hover:text-slate-700'" class="py-2.5 rounded-lg text-sm font-bold flex flex-col items-center justify-center gap-1 transition">
                        <i data-lucide="qr-code" class="w-5 h-5"></i> QRIS
                    </button>
                    <button @click="metode = 'transfer'; bayar = totalAkhir;" :class="metode === 'transfer' ? 'bg-white text-blue-600 shadow-sm border border-slate-200' : 'text-slate-500 hover:text-slate-700'" class="py-2.5 rounded-lg text-sm font-bold flex flex-col items-center justify-center gap-1 transition">
                        <i data-lucide="credit-card" class="w-5 h-5"></i> Transfer
                    </button>
                </div>

                <!-- Ringkasan Belanja -->
                <div class="space-y-1.5 text-xs text-slate-600">
                    <template x-for="item in cart" :key="item.id">
                        <div class="flex justify-between">
                            <span class="truncate pr-4" x-text="`${item.nama} ×${item.qty}`"></span>
                            <span class="font-medium shrink-0" x-text="formatRp(item.harga * item.qty)"></span>
                        </div>
                    </template>
                    <div class="border-t border-slate-100 pt-2 flex justify-between font-semibold mt-2 text-sm text-slate-500">
                        <span>Subtotal</span>
                        <span x-text="formatRp(subtotal)"></span>
                    </div>
                    <div class="flex justify-between font-bold text-blue-600 text-lg pt-1">
                        <span>Total Bayar</span>
                        <span x-text="formatRp(totalAkhir)"></span>
                    </div>
                </div>

                <!-- Diskon -->
                <div class="space-y-3 bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <div class="flex justify-between items-center">
                        <label class="text-sm font-bold text-slate-800">Diskon</label>
                        <div class="flex bg-slate-200 p-0.5 rounded-lg">
                            <button @click="diskonTipe = 'persen'" :class="diskonTipe === 'persen' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="px-3 py-1 text-xs font-bold rounded-md transition">%</button>
                            <button @click="diskonTipe = 'nominal'" :class="diskonTipe === 'nominal' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="px-3 py-1 text-xs font-bold rounded-md transition">Rp</button>
                        </div>
                    </div>
                    
                    <div x-show="diskonTipe === 'persen'" class="space-y-3">
                        <div class="grid grid-cols-6 gap-1.5">
                            <button type="button" @click="setDiskonPersen(5)" class="py-1.5 border border-slate-200 rounded text-xs font-semibold hover:bg-blue-50 bg-white transition">5%</button>
                            <button type="button" @click="setDiskonPersen(10)" class="py-1.5 border border-slate-200 rounded text-xs font-semibold hover:bg-blue-50 bg-white transition">10%</button>
                            <button type="button" @click="setDiskonPersen(15)" class="py-1.5 border border-slate-200 rounded text-xs font-semibold hover:bg-blue-50 bg-white transition">15%</button>
                            <button type="button" @click="setDiskonPersen(20)" class="py-1.5 border border-slate-200 rounded text-xs font-semibold hover:bg-blue-50 bg-white transition">20%</button>
                            <button type="button" @click="setDiskonPersen(25)" class="py-1.5 border border-slate-200 rounded text-xs font-semibold hover:bg-blue-50 bg-white transition">25%</button>
                            <button type="button" @click="diskonPersen = ''; diskonNominal = '';" class="py-1.5 border border-red-200 text-red-600 rounded text-xs font-semibold hover:bg-red-50 bg-white transition">Reset</button>
                        </div>
                        <div class="relative">
                            <input type="number" x-model.number="diskonPersen" placeholder="Atau ketik persen (0-100)" class="w-full pl-4 pr-8 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white shadow-sm">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-bold">%</span>
                        </div>
                    </div>

                    <div x-show="diskonTipe === 'nominal'" class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm font-semibold">Rp</span>
                        <input type="number" x-model.number="diskonNominal" placeholder="Ketik nominal diskon" class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white shadow-sm">
                    </div>
                </div>

                <!-- Konten Mode Cash -->
                <div x-show="metode === 'cash'" class="space-y-3 pt-2">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Nominal Pembayaran</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-lg font-bold">Rp</span>
                        <input type="number" x-model.number="bayar" class="w-full pl-12 pr-4 py-3 text-xl font-bold border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-sm">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2 mt-3">
                        <template x-for="sug in suggestedAmounts" :key="sug">
                            <button @click="bayar = sug" class="py-2.5 border border-slate-200 bg-white hover:bg-slate-50 rounded-lg text-sm font-bold text-slate-700 transition shadow-sm" x-text="formatRp(sug)"></button>
                        </template>
                    </div>

                    <div x-show="kembalian >= 0 && bayar > 0" class="mt-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex justify-between font-bold text-lg">
                        <span>Kembali</span>
                        <span x-text="formatRp(kembalian)"></span>
                    </div>
                    <div x-show="kembalian < 0 && bayar > 0" class="mt-4 p-3 bg-red-50 border border-red-200 text-red-600 rounded-xl text-sm font-medium text-center">
                        Nominal kurang Rp <span x-text="formatRp(Math.abs(kembalian)).replace('Rp ', '')"></span>
                    </div>
                </div>

                <!-- Konten Mode QRIS -->
                <div x-show="metode === 'qris'" class="pt-4 pb-2 border border-slate-200 rounded-xl flex flex-col items-center justify-center bg-slate-50 shadow-sm">
                    @if(isset($pengaturan) && $pengaturan->qris_image)
                    <div class="w-48 h-48 border border-slate-200 rounded-xl overflow-hidden mb-4 shadow-sm bg-white p-2">
                        <img src="{{ asset('storage/' . $pengaturan->qris_image) }}" alt="QRIS" class="w-full h-full object-contain">
                    </div>
                    @else
                    <div class="w-48 h-48 bg-white border border-slate-200 rounded-xl flex items-center justify-center p-2 mb-4 relative overflow-hidden shadow-sm">
                        <!-- Simulated QR Code visual -->
                        <div class="grid grid-cols-3 grid-rows-3 gap-1 w-full h-full p-2">
                            <div class="bg-slate-800 rounded-tl-lg"></div><div class="bg-slate-200"></div><div class="bg-slate-800 rounded-tr-lg"></div>
                            <div class="bg-slate-100"></div><div class="bg-slate-800 rounded-lg"></div><div class="bg-slate-300"></div>
                            <div class="bg-slate-800 rounded-bl-lg"></div><div class="bg-slate-300"></div><div class="bg-slate-800 rounded-br-lg"></div>
                        </div>
                        <div class="absolute inset-0 bg-white/30 backdrop-blur-[1px] flex items-center justify-center">
                            <i data-lucide="scan-line" class="w-16 h-16 text-slate-800/80 drop-shadow-md"></i>
                        </div>
                    </div>
                    @endif
                    <p class="text-slate-500 text-sm mb-1">Scan QR Code untuk membayar</p>
                    <p class="text-blue-600 font-bold text-xl" x-text="formatRp(totalAkhir)"></p>
                </div>

                <!-- Konten Mode Transfer -->
                <div x-show="metode === 'transfer'" class="pt-6 pb-6 border border-slate-200 rounded-xl flex flex-col items-center justify-center bg-slate-50 shadow-sm space-y-2">
                    <p class="text-slate-500 text-sm">Transfer ke rekening</p>
                    <p class="font-bold text-slate-800 text-lg tracking-wide">{{ isset($pengaturan) ? $pengaturan->bank_nama . ' — ' . $pengaturan->bank_rekening : 'BCA — 4521 8899 0012' }}</p>
                    <p class="text-sm text-slate-500">a.n. {{ isset($pengaturan) ? $pengaturan->bank_atas_nama : 'Raket Murah Jogja' }}</p>
                    <div class="w-full border-t border-dashed border-slate-300 my-2"></div>
                    <p class="text-blue-600 font-bold text-xl mt-2" x-text="formatRp(totalAkhir)"></p>
                </div>

            </div>

            <!-- Footer Modal Pembayaran -->
            <div class="p-4 border-t border-slate-100 bg-white rounded-b-2xl flex gap-3 shadow-[0_-10px_20px_-15px_rgba(0,0,0,0.05)] z-10">
                <button @click="closePaymentModal()" class="flex-1 px-4 py-3 border border-slate-200 text-slate-600 bg-white hover:bg-slate-50 rounded-xl font-bold transition text-sm">Batal</button>
                <button @click="processPayment()" :disabled="!isReadyToPay" class="flex-[1.5] px-4 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white rounded-xl font-bold shadow-lg shadow-blue-600/30 transition flex items-center justify-center gap-2 text-sm">
                    <i data-lucide="check-circle" class="w-4 h-4"></i> Konfirmasi Bayar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Transaksi Berhasil (Struk) -->
    <div x-show="showSuccessModal" class="fixed inset-0 z-[200] bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;" x-transition>
        <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden relative">
            <button @click="resetPOS()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 transition z-10 bg-white/80 rounded-full p-1"><i data-lucide="x" class="w-5 h-5"></i></button>
            
            <div class="pt-10 pb-6 px-6 flex flex-col items-center border-b border-dashed border-slate-300 bg-white relative">
                <!-- Ornamen bergerigi (struk) dihilangkan agar lebih clean dan mirip Figma -->
                <div class="w-16 h-16 bg-emerald-100 text-emerald-500 rounded-full flex items-center justify-center mb-4">
                    <i data-lucide="check" class="w-8 h-8 stroke-[3]"></i>
                </div>
                <h2 class="font-bold text-xl text-slate-800">Pembayaran Berhasil</h2>
                <p class="text-slate-400 text-sm mt-1 uppercase tracking-wider" x-text="trxId"></p>
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
                        <span x-text="trxDate"></span>
                        <span>Administrator</span>
                    </div>
                </div>

                <div class="space-y-4 font-mono text-xs">
                    <template x-for="item in completedCart" :key="item.id">
                        <div>
                            <div class="text-slate-800 font-bold truncate" x-text="item.nama"></div>
                            <div class="flex justify-between text-slate-500 mt-1">
                                <span x-text="`${item.qty} x ${formatRp(item.harga)}`"></span>
                                <span class="font-bold text-slate-800" x-text="formatRp(item.qty * item.harga)"></span>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="w-full border-t border-dashed border-slate-300 my-4"></div>

                <div class="space-y-2 font-mono text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal</span>
                        <span x-text="formatRp(completedSubtotal)"></span>
                    </div>
                    <div x-show="completedDiskon > 0" class="flex justify-between text-red-500 font-bold">
                        <span>Diskon</span>
                        <span x-text="'-' + formatRp(completedDiskon)"></span>
                    </div>
                    <div class="flex justify-between font-black text-base text-slate-800 mt-2 pt-2 border-t border-slate-200">
                        <span>TOTAL BAYAR</span>
                        <span x-text="formatRp(completedTotal)"></span>
                    </div>
                    
                    <div class="flex justify-between text-slate-500 mt-4">
                        <span>Metode</span>
                        <span class="uppercase font-bold" x-text="completedMetode"></span>
                    </div>

                    <!-- Rincian Cash jika metode cash -->
                    <template x-if="completedMetode === 'cash'">
                        <div>
                            <div class="flex justify-between text-slate-500 mt-1">
                                <span>Tunai</span>
                                <span x-text="formatRp(completedBayar)"></span>
                            </div>
                            <div class="flex justify-between text-slate-500 mt-1">
                                <span>Kembali</span>
                                <span x-text="formatRp(completedBayar - completedTotal)"></span>
                            </div>
                        </div>
                    </template>
                </div>
                
                <div class="w-full border-t border-dashed border-slate-300 mt-6 mb-4"></div>
                <p class="text-center text-[10px] text-slate-400 font-mono">{{ isset($pengaturan) ? $pengaturan->footer_struk : 'Terima kasih atas kunjungan Anda!' }}</p>
            </div>

            <!-- Footer Modal Berhasil -->
            <div class="p-4 bg-white flex gap-3 shadow-[0_-10px_20px_-15px_rgba(0,0,0,0.05)] z-10 border-t border-slate-100">
                <button @click="resetPOS()" class="flex-1 px-4 py-3 border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 rounded-xl font-bold transition text-sm">
                    Transaksi Baru
                </button>
                <button @click="printReceipt()" class="flex-1 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg shadow-blue-600/30 transition flex items-center justify-center gap-2 text-sm">
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
    function posApp() {
        return {
            produks: @json($produks),
            search: '',
            kategoriFilter: 0,
            cart: [],
            
            showPaymentModal: false,
            metode: 'cash',
            
            // State Diskon
            diskonTipe: 'persen', // 'persen' atau 'nominal'
            diskonPersen: '',
            diskonNominal: '',

            bayar: '',
            
            // State untuk Struk Modal Berhasil
            showSuccessModal: false,
            trxId: '',
            trxDate: '',
            completedCart: [],
            completedSubtotal: 0,
            completedDiskon: 0,
            completedTotal: 0,
            completedMetode: '',
            completedBayar: 0,
            
            get filteredProduks() {
                return this.produks.filter(p => {
                    const matchSearch = p.nama.toLowerCase().includes(this.search.toLowerCase()) || p.kode.toLowerCase().includes(this.search.toLowerCase());
                    const matchKat = this.kategoriFilter === 0 || p.kategori_id === this.kategoriFilter;
                    return matchSearch && matchKat;
                });
            },
            
            get cartItemCount() {
                return this.cart.reduce((sum, item) => sum + item.qty, 0);
            },
            
            getCartQty(id) {
                const item = this.cart.find(i => i.id === id);
                return item ? item.qty : 0;
            },

            get subtotal() {
                return this.cart.reduce((sum, item) => sum + (item.harga * item.qty), 0);
            },

            // Kalkulasi Diskon Otomatis
            get nilaiDiskon() {
                if (this.diskonTipe === 'persen') {
                    let p = parseFloat(this.diskonPersen) || 0;
                    if (p > 100) p = 100;
                    if (p < 0) p = 0;
                    return (this.subtotal * p) / 100;
                } else {
                    let n = parseInt(this.diskonNominal) || 0;
                    return n > this.subtotal ? this.subtotal : n;
                }
            },

            get totalAkhir() {
                let final = this.subtotal - this.nilaiDiskon;
                return final < 0 ? 0 : Math.round(final);
            },

            // Dynamic Preset Cash Amounts
            get suggestedAmounts() {
                const total = this.totalAkhir;
                if (total === 0) return [0, 50000, 100000, 150000];
                
                let amounts = [total]; // Uang pas selalu ada
                
                // Pembulatan ke 50.000 atau 100.000 terdekat ke atas
                let next50k = Math.ceil(total / 50000) * 50000;
                if (next50k > total && !amounts.includes(next50k)) amounts.push(next50k);
                
                let next100k = Math.ceil(total / 100000) * 100000;
                if (next100k > total && !amounts.includes(next100k)) amounts.push(next100k);
                
                // Jika masih kurang dari 4 opsi, tambahkan pecahan ribuan/puluhan ribu
                let next10k = Math.ceil(total / 10000) * 10000;
                if (next10k > total && !amounts.includes(next10k)) amounts.push(next10k);
                
                let next20k = Math.ceil(total / 20000) * 20000;
                if (next20k > total && !amounts.includes(next20k)) amounts.push(next20k);
                
                return amounts.sort((a,b) => a - b).slice(0, 4); // Maksimal 4 tombol
            },

            get kembalian() {
                let b = this.bayar ? parseInt(this.bayar) : 0;
                return b - this.totalAkhir;
            },

            get isReadyToPay() {
                if (this.metode === 'cash') {
                    return this.bayar && this.kembalian >= 0;
                }
                return true;
            },
            
            addToCart(produk) {
                const item = this.cart.find(i => i.id === produk.id);
                if (item) {
                    if (item.qty < produk.stok) item.qty++;
                } else {
                    if (produk.stok > 0) {
                        this.cart.push({ ...produk, qty: 1 });
                    } else {
                        alert('Stok produk habis!');
                    }
                }
            },
            
            updateQty(id, change) {
                const item = this.cart.find(i => i.id === id);
                if (!item) return;
                
                const newQty = item.qty + change;
                if (newQty <= 0) {
                    this.cart = this.cart.filter(i => i.id !== id);
                } else if (newQty <= item.stok) {
                    item.qty = newQty;
                }
            },
            
            formatRp(angka) {
                return 'Rp' + new Intl.NumberFormat('id-ID').format(angka).replace(/,/g, '.');
            },
            
            openPaymentModal() {
                this.showPaymentModal = true;
                this.metode = 'cash';
                this.diskonTipe = 'persen';
                this.diskonPersen = '';
                this.diskonNominal = '';
                this.updateBayarDefault();
                setTimeout(() => lucide.createIcons(), 50);
            },
            
            closePaymentModal() {
                this.showPaymentModal = false;
            },

            setDiskonPersen(val) {
                this.diskonTipe = 'persen';
                this.diskonPersen = val;
                this.updateBayarDefault();
            },

            updateBayarDefault() {
                if (this.metode === 'cash') {
                    this.bayar = this.totalAkhir; // Set bayar pas secara otomatis
                } else {
                    this.bayar = this.totalAkhir;
                }
            },

            init() {
                this.$watch('totalAkhir', (val) => {
                    if (this.metode !== 'cash') {
                        this.bayar = val;
                    }
                });
            },
            
            async processPayment() {
                if (!this.isReadyToPay) return;
                
                try {
                    const res = await fetch("{{ route('pos.checkout') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            subtotal: this.subtotal,
                            diskon: this.nilaiDiskon,
                            total: this.totalAkhir,
                            metode: this.metode,
                            bayar: this.metode === 'cash' ? this.bayar : this.totalAkhir,
                            items: this.cart
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        // Persiapkan data struk
                        this.trxId = data.transaksi_id;
                        let d = new Date();
                        this.trxDate = `${String(d.getDate()).padStart(2,'0')}/${String(d.getMonth()+1).padStart(2,'0')}/${d.getFullYear()} ${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`;
                        
                        this.completedCart = [...this.cart];
                        this.completedSubtotal = this.subtotal;
                        this.completedDiskon = this.nilaiDiskon;
                        this.completedTotal = this.totalAkhir;
                        this.completedMetode = this.metode;
                        this.completedBayar = this.metode === 'cash' ? this.bayar : this.totalAkhir;
                        
                        // Tutup modal bayar, tampilkan sukses
                        this.showPaymentModal = false;
                        this.showSuccessModal = true;
                        
                        // Icon init inside new modal
                        setTimeout(() => lucide.createIcons(), 50);
                    }
                } catch (e) {
                    alert('Terjadi kesalahan koneksi.');
                }
            },

            resetPOS() {
                this.cart = [];
                this.showSuccessModal = false;
                location.reload(); // Reload to refresh stock
            },

            printReceipt() {
                window.print();
            }
        }
    }
</script>
@endsection
