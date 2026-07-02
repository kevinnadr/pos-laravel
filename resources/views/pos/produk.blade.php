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

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px] font-bold tracking-wider">
                    <th class="p-4">Produk</th>
                    <th class="p-4">Kode</th>
                    <th class="p-4">Kategori</th>
                    <th class="p-4">Harga Jual</th>
                    <th class="p-4">Harga Modal</th>
                    <th class="p-4 text-center">Stok</th>
                    <th class="p-4 text-center">Min. Stok</th>
                    <th class="p-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <template x-for="p in filteredProduks" :key="p.id">
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="p-4 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center overflow-hidden shrink-0 border border-slate-200">
                                <template x-if="p.foto">
                                    <img :src="`/storage/${p.foto}`" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!p.foto">
                                    <i data-lucide="image" class="w-5 h-5 text-slate-400"></i>
                                </template>
                            </div>
                            <span class="font-bold text-sm text-slate-800" x-text="p.nama"></span>
                        </td>
                        <td class="p-4 text-sm font-semibold text-blue-600" x-text="p.kode"></td>
                        <td class="p-4">
                            <span class="bg-slate-100 text-slate-600 text-xs px-2.5 py-1 rounded-full font-medium border border-slate-200" x-text="p.kategori.nama"></span>
                        </td>
                        <td class="p-4 font-bold text-sm text-slate-800" x-text="formatRupiah(p.harga)"></td>
                        <td class="p-4 text-sm text-slate-500" x-text="formatRupiah(p.harga_modal)"></td>
                        <td class="p-4 text-center">
                            <span x-text="p.stok" class="text-xs font-bold px-2 py-1 rounded-md" 
                                :class="p.stok <= p.min_stok ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700'">
                            </span>
                            <template x-if="p.stok <= p.min_stok">
                                <i data-lucide="alert-triangle" class="w-3 h-3 text-amber-500 inline ml-1"></i>
                            </template>
                        </td>
                        <td class="p-4 text-center text-sm text-slate-500" x-text="p.min_stok"></td>
                        <td class="p-4 flex items-center justify-end gap-2">
                            <button @click="openModal(p)" class="text-slate-500 hover:text-blue-600 px-3 py-1.5 border border-slate-200 rounded-md text-sm font-medium hover:bg-slate-50 flex items-center gap-1 transition">
                                <i data-lucide="edit-2" class="w-3.5 h-3.5"></i> Edit
                            </button>
                            <form :action="`/produk/${p.id}`" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-600 px-3 py-1.5 border border-red-100 bg-red-50 rounded-md text-sm font-medium hover:bg-red-100 flex items-center gap-1 transition">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                </template>
                <tr x-show="filteredProduks.length === 0">
                    <td colspan="8" class="p-8 text-center text-slate-500">Produk tidak ditemukan.</td>
                </tr>
            </tbody>
        </table>
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
                        <input type="text" name="nama" x-model="form.nama" required placeholder="cth. Aqua 600ml" class="w-full px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Kategori</label>
                        <select name="kategori_id" x-model="form.kategori_id" required class="w-full px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                            <option value="">Pilih Kategori</option>
                            @foreach($kategoris as $kat)
                            <option value="{{ $kat->id }}">{{ $kat->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Harga Jual (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm">Rp</span>
                            <input type="number" name="harga" x-model="form.harga" required class="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Harga Modal / HPP (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm">Rp</span>
                            <input type="number" name="harga_modal" x-model="form.harga_modal" required class="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Stok</label>
                        <input type="number" name="stok" x-model="form.stok" required class="w-full px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Minimum Stok (Alert)</label>
                        <input type="number" name="min_stok" x-model="form.min_stok" required class="w-full px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
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
            search: '',
            selectedCategory: '{{ request()->query('kategori') ?? '' }}',
            showModal: false,
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
            
            openModal(produk = null) {
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
