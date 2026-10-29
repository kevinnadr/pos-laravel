@extends('pos.layout')

@section('content')
<div class="p-6" x-data="produkApp()">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Manajemen Produk</h1>
            <p class="text-slate-500 text-sm mt-1">{{ count($produks) }} produk terdaftar</p>
        </div>
        <button @click="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium flex items-center gap-2 transition shadow-sm">
            <i data-lucide="plus" class="w-4 h-4"></i> Tambah Produk
        </button>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-emerald-50 text-emerald-600 p-3 rounded-lg text-sm flex items-center gap-2 border border-emerald-200">
        <i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}
    </div>
    @endif
    
    @if($errors->any())
    <div class="mb-4 bg-red-50 text-red-600 p-3 rounded-lg text-sm flex items-center gap-2 border border-red-200">
        <i data-lucide="alert-circle" class="w-4 h-4"></i> Ada kesalahan pada input.
    </div>
    @endif

    <div class="mb-6 bg-white p-3 rounded-xl shadow-sm border border-slate-200 flex gap-4">
        <div class="relative flex-1">
            <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" x-model="search" placeholder="Cari nama / kode produk..." class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500">
        </div>
        <div class="w-48">
            <select x-model="selectedCategory" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500">
                <option value="">Semua Kategori</option>
                @foreach($kategoris as $kat)
                <option value="{{ $kat->id }}">{{ $kat->nama }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 overflow-hidden mb-8">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-white shadow-sm border-b-2 border-slate-100">
                    <tr class="text-slate-700 uppercase text-xs font-extrabold tracking-wider">
                        <th class="px-5 py-5 rounded-tl-xl text-center">Produk</th>
                        <th class="px-5 py-5">Kode</th>
                        <th class="px-5 py-5">Kategori</th>
                        <th class="px-5 py-5">Harga Jual</th>
                        <th class="px-5 py-5">Harga Modal</th>
                        <th class="px-5 py-5 text-center">Stok</th>
                        <th class="px-5 py-5 text-center">Min. Stok</th>
                        <th class="px-5 py-5 text-center rounded-tr-xl">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="p in filteredProduks" :key="p.id">
                        <tr class="hover:bg-blue-50/40 transition-colors group">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-11 h-11 rounded-full bg-slate-100 flex items-center justify-center overflow-hidden shrink-0 border border-slate-200 shadow-inner">
                                        <template x-if="p.foto">
                                            <img :src="`/storage/${p.foto}`" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!p.foto">
                                            <i data-lucide="image" class="w-5 h-5 text-slate-300"></i>
                                        </template>
                                    </div>
                                    <span class="font-bold text-sm text-slate-800 group-hover:text-blue-600 transition-colors" x-text="p.nama"></span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-sm font-bold text-slate-500" x-text="p.kode"></td>
                            <td class="px-5 py-4">
                                <span class="bg-slate-100 text-slate-600 text-[11px] px-2.5 py-1 rounded-md font-bold uppercase tracking-wider" x-text="p.kategori.nama"></span>
                            </td>
                            <td class="px-5 py-4 font-bold text-sm text-slate-800" x-text="formatRupiah(p.harga)"></td>
                            <td class="px-5 py-4 text-sm font-medium text-slate-400" x-text="formatRupiah(p.harga_modal)"></td>
                            <td class="px-5 py-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span x-text="p.stok" class="text-xs font-bold px-3 py-1.5 rounded-full" 
                                        :class="p.stok <= p.min_stok ? 'bg-red-100 text-red-600' : 'bg-emerald-100 text-emerald-600'">
                                    </span>
                                    <template x-if="p.stok <= p.min_stok">
                                        <i data-lucide="alert-triangle" class="w-4 h-4 text-red-500 animate-pulse"></i>
                                    </template>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-center text-sm font-semibold text-slate-300" x-text="p.min_stok"></td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-3 text-slate-400">
                                    <button @click="openModal(p)" class="hover:text-blue-600 p-2 rounded-full hover:bg-blue-50 transition-colors" title="Edit">
                                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                                    </button>
                                    <form :action="`/produk/${p.id}`" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="hover:text-red-600 p-2 rounded-full hover:bg-red-50 transition-colors" title="Hapus">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filteredProduks.length === 0">
                        <td colspan="8" class="py-16 text-center text-slate-500">
                            <i data-lucide="box" class="w-12 h-12 mx-auto mb-3 text-slate-300"></i>
                            <p class="font-bold text-slate-600">Produk tidak ditemukan</p>
                            <p class="text-xs text-slate-400 mt-1">Coba kata kunci lain atau hapus filter kategori.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form Produk -->
    <div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 bg-slate-900/50 flex items-center justify-center p-4">
        <div @click.away="closeModal()" class="bg-white w-full max-w-2xl rounded-2xl shadow-xl overflow-hidden transform transition-all">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-bold text-lg text-slate-800" x-text="isEdit ? 'Edit Produk' : 'Tambah Produk'"></h3>
                <button @click="closeModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            
            <form :action="isEdit ? `/produk/${form.id}` : '{{ route('produk.store') }}'" method="POST" enctype="multipart/form-data">
                @csrf
                <template x-if="isEdit">
                    @method('PUT')
                </template>
                
                <div class="p-6 grid grid-cols-2 gap-4 max-h-[70vh] overflow-y-auto">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Kode Produk</label>
                        <input type="text" name="kode" x-model="form.kode" required class="w-full px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Nama Produk</label>
                        <input type="text" name="nama" x-model="form.nama" required placeholder="cth. Yonex Arcos 98" class="w-full px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div class="relative">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Kategori</label>
                        <input type="hidden" name="kategori_id" :value="form.kategori_id" required>
                        <div class="relative">
                            <input 
                                type="text" 
                                x-model="katSearch" 
                                @focus="katOpen = true" 
                                @click="katOpen = true"
                                @input="katOpen = true; katHighlight = 0"
                                @blur="setTimeout(() => { katOpen = false }, 150)"
                                @keydown.arrow-down.prevent="katHighlight = Math.min(katHighlight + 1, filteredKategoris.length - 1)"
                                @keydown.arrow-up.prevent="katHighlight = Math.max(katHighlight - 1, 0)"
                                @keydown.enter.prevent="if(filteredKategoris.length > 0 && katHighlight >= 0) selectKategori(filteredKategoris[katHighlight])"
                                @keydown.escape="katOpen = false"
                                placeholder="Ketik atau pilih kategori..."
                                autocomplete="off"
                                class="w-full px-4 py-2 pr-9 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            >
                            <button type="button" @mousedown.prevent="katOpen = !katOpen" tabindex="-1" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1">
                                <svg class="w-4 h-4 transition-transform" :class="katOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </div>
                        <div 
                            x-show="katOpen" 
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute z-50 mt-1 w-full bg-white border border-slate-200 rounded-lg shadow-lg max-h-48 overflow-y-auto"
                            style="display: none;"
                        >
                            <template x-for="(kat, index) in filteredKategoris" :key="kat.id">
                                <div 
                                    @mousedown.prevent="selectKategori(kat)" 
                                    @mouseenter="katHighlight = index"
                                    class="px-4 py-2.5 text-sm cursor-pointer flex items-center justify-between transition-colors"
                                    :class="{
                                        'bg-blue-50 text-blue-700': katHighlight === index,
                                        'text-slate-700 hover:bg-slate-50': katHighlight !== index,
                                        'font-semibold': form.kategori_id == kat.id
                                    }"
                                >
                                    <span x-text="kat.nama"></span>
                                    <svg x-show="form.kategori_id == kat.id" class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            </template>
                            <div x-show="filteredKategoris.length === 0" class="px-4 py-3 text-sm text-slate-400 text-center">
                                Kategori tidak ditemukan
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Harga Jual (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm">Rp</span>
                            <input type="hidden" name="harga" :value="form.harga">
                            <input type="text" 
                                :value="formatDot(form.harga)" 
                                @input="form.harga = parseDot($event.target.value); $event.target.value = formatDot(form.harga)"
                                required inputmode="numeric"
                                placeholder="0"
                                class="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Harga Modal / HPP (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm">Rp</span>
                            <input type="hidden" name="harga_modal" :value="form.harga_modal">
                            <input type="text" 
                                :value="formatDot(form.harga_modal)" 
                                @input="form.harga_modal = parseDot($event.target.value); $event.target.value = formatDot(form.harga_modal)"
                                required inputmode="numeric"
                                placeholder="0"
                                class="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Stok</label>
                        <input type="hidden" name="stok" :value="form.stok">
                        <input type="text" 
                            :value="formatDot(form.stok)" 
                            @input="form.stok = parseDot($event.target.value); $event.target.value = formatDot(form.stok)"
                            required inputmode="numeric"
                            placeholder="0"
                            class="w-full px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Minimum Stok (Alert)</label>
                        <input type="hidden" name="min_stok" :value="form.min_stok">
                        <input type="text" 
                            :value="formatDot(form.min_stok)" 
                            @input="form.min_stok = parseDot($event.target.value); $event.target.value = formatDot(form.min_stok)"
                            required inputmode="numeric"
                            placeholder="0"
                            class="w-full px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div class="col-span-2 mt-2">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Foto Produk</label>
                        <div class="flex items-center gap-4">
                            <div class="w-20 h-20 border-2 border-dashed border-slate-300 rounded-xl flex items-center justify-center bg-slate-50 overflow-hidden shrink-0">
                                <template x-if="imagePreview">
                                    <img :src="imagePreview" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!imagePreview">
                                    <i data-lucide="camera" class="w-6 h-6 text-slate-400"></i>
                                </template>
                            </div>
                            <div>
                                <label class="cursor-pointer bg-white border border-slate-200 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-50 inline-flex items-center gap-2">
                                    <i data-lucide="upload" class="w-4 h-4"></i> Pilih dari Perangkat
                                    <input type="file" name="foto" class="hidden" accept="image/jpeg, image/png, image/webp" @change="previewFile">
                                </label>
                                <p class="text-xs text-slate-400 mt-2">JPG, PNG, atau WebP. Maks 2MB.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" @click="closeModal()" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm shadow-blue-600/20" x-text="isEdit ? 'Simpan Produk' : 'Simpan Produk'"></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function produkApp() {
        return {
            produks: @json($produks),
            kategoris: @json($kategoris),
            search: '',
            selectedCategory: '{{ request()->query('kategori') ?? '' }}',
            showModal: false,
            katSearch: '',
            katOpen: false,
            katHighlight: 0,

            init() {
                this.$watch('search', () => {
                    this.$nextTick(() => lucide.createIcons());
                });
                this.$watch('selectedCategory', () => {
                    this.$nextTick(() => lucide.createIcons());
                });
            },

            get filteredKategoris() {
                if (this.katSearch === '' || this.kategoris.some(k => k.nama === this.katSearch)) {
                    return this.kategoris;
                }
                const s = this.katSearch.toLowerCase();
                return this.kategoris.filter(k => k.nama.toLowerCase().includes(s));
            },

            selectKategori(kat) {
                this.form.kategori_id = kat.id;
                this.katSearch = kat.nama;
                this.katOpen = false;
                this.katHighlight = 0;
            },

            isEdit: false,
            imagePreview: null,
            form: {
                id: null,
                kode: '',
                nama: '',
                kategori_id: '',
                harga: 0,
                harga_modal: 0,
                stok: 0,
                min_stok: 0,
            },
            
            get filteredProduks() {
                let filtered = this.produks;
                
                if (this.selectedCategory !== '') {
                    filtered = filtered.filter(p => p.kategori_id == this.selectedCategory);
                }
                
                if (this.search !== '') {
                    const searchLower = this.search.toLowerCase();
                    filtered = filtered.filter(p => 
                        p.nama.toLowerCase().includes(searchLower) || 
                        p.kode.toLowerCase().includes(searchLower)
                    );
                }
                
                return filtered;
            },
            
            formatRupiah(number) {
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(number);
            },

            formatDot(value) {
                const num = parseInt(String(value).replace(/\D/g, ''), 10);
                if (isNaN(num) || num === 0) return '';
                return num.toLocaleString('id-ID');
            },

            parseDot(value) {
                const num = parseInt(String(value).replace(/\D/g, ''), 10);
                return isNaN(num) ? 0 : num;
            },
            
            openModal(produk = null) {
                this.katOpen = false;
                this.katHighlight = 0;
                if (produk) {
                    this.isEdit = true;
                    this.form.id = produk.id;
                    this.form.kode = produk.kode;
                    this.form.nama = produk.nama;
                    this.form.kategori_id = produk.kategori_id;
                    this.form.harga = produk.harga;
                    this.form.harga_modal = produk.harga_modal;
                    this.form.stok = produk.stok;
                    this.form.min_stok = produk.min_stok;
                    this.imagePreview = produk.foto ? `/storage/${produk.foto}` : null;
                    // Set category name in search field
                    const kat = this.kategoris.find(k => k.id == produk.kategori_id);
                    this.katSearch = kat ? kat.nama : '';
                } else {
                    this.isEdit = false;
                    this.form.id = null;
                    this.form.kode = `PRD${Math.floor(Math.random() * 10000).toString().padStart(4, '0')}`;
                    this.form.nama = '';
                    this.form.kategori_id = '';
                    this.form.harga = '';
                    this.form.harga_modal = '';
                    this.form.stok = '';
                    this.form.min_stok = '';
                    this.imagePreview = null;
                    this.katSearch = '';
                }
                this.showModal = true;
                setTimeout(() => lucide.createIcons(), 50);
            },
            
            closeModal() {
                this.showModal = false;
            },
            
            previewFile(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.imagePreview = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            }
        }
    }


</script>
@endsection
