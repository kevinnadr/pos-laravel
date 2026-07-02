@extends('pos.layout')

@section('scripts')
@endsection

@section('content')
<div class="p-6" x-data="kategoriApp()">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Kategori</h1>
            <p class="text-slate-500 text-sm mt-1">{{ $kategoris->count() }} kategori terdaftar</p>
        </div>
        <button @click="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium flex items-center gap-2 transition shadow-sm">
            <i data-lucide="plus" class="w-4 h-4"></i> Tambah Kategori
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

    <div class="mb-6 bg-white p-3 rounded-xl shadow-sm border border-slate-200">
        <div class="relative max-w-md">
            <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" x-model="search" placeholder="Cari kategori..." class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500">
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-xs font-semibold tracking-wider">
                    <th class="p-4 w-16">No</th>
                    <th class="p-4">Nama Kategori</th>
                    <th class="p-4">Deskripsi</th>
                    <th class="p-4">Jumlah Produk</th>
                    <th class="p-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <template x-for="(k, index) in filteredKategoris" :key="k.id">
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="p-4 text-sm text-slate-500" x-text="index + 1"></td>
                        <td class="p-4 font-bold text-slate-800" x-text="k.nama"></td>
                        <td class="p-4 text-sm text-slate-500" x-text="k.deskripsi || '-'"></td>
                        <td class="p-4">
                            <span class="bg-blue-50 text-blue-600 border border-blue-200 text-xs font-bold px-2.5 py-1 rounded-full" x-text="k.produks_count + ' produk'"></span>
                        </td>
                        <td class="p-4 flex items-center justify-end gap-2">
                            <button @click="openModal(k)" class="text-slate-500 hover:text-blue-600 px-3 py-1.5 border border-slate-200 rounded-md text-sm font-medium hover:bg-slate-50 flex items-center gap-1 transition">
                                <i data-lucide="edit-2" class="w-3.5 h-3.5"></i> Edit
                            </button>
                            <form :action="`/kategori/${k.id}`" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini? Semua produk di dalamnya mungkin akan kehilangan referensi kategorinya.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-600 px-3 py-1.5 border border-red-100 bg-red-50 rounded-md text-sm font-medium hover:bg-red-100 flex items-center gap-1 transition">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                </template>
                <tr x-show="filteredKategoris.length === 0">
                    <td colspan="5" class="p-8 text-center text-slate-500">Kategori tidak ditemukan.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Modal Form Kategori -->
    <div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 bg-slate-900/50 flex items-center justify-center p-4">
        <div @click.away="closeModal()" class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden transform transition-all">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-bold text-lg text-slate-800" x-text="isEdit ? 'Edit Kategori' : 'Tambah Kategori'"></h3>
                <button @click="closeModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            
            <form :action="isEdit ? `/kategori/${form.id}` : '{{ route('kategori.store') }}'" method="POST">
                @csrf
                <template x-if="isEdit">
                    @method('PUT')
                </template>
                
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Nama Kategori</label>
                        <input type="text" name="nama" x-model="form.nama" required placeholder="cth. Minuman" class="w-full px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Deskripsi</label>
                        <input type="text" name="deskripsi" x-model="form.deskripsi" placeholder="Deskripsi singkat kategori" class="w-full px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" @click="closeModal()" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm shadow-blue-600/20">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function kategoriApp() {
        return {
            kategoris: @json($kategoris),
            search: '',
            showModal: false,
            isEdit: false,
            form: {
                id: null,
                nama: '',
                deskripsi: ''
            },
            
            get filteredKategoris() {
                if (this.search === '') return this.kategoris;
                return this.kategoris.filter(k => 
                    k.nama.toLowerCase().includes(this.search.toLowerCase()) || 
                    (k.deskripsi && k.deskripsi.toLowerCase().includes(this.search.toLowerCase()))
                );
            },
            
            openModal(kategori = null) {
                if (kategori) {
                    this.isEdit = true;
                    this.form.id = kategori.id;
                    this.form.nama = kategori.nama;
                    this.form.deskripsi = kategori.deskripsi || '';
                } else {
                    this.isEdit = false;
                    this.form.id = null;
                    this.form.nama = '';
                    this.form.deskripsi = '';
                }
                this.showModal = true;
                // Re-init lucide icons incase DOM updates
                setTimeout(() => lucide.createIcons(), 50);
            },
            
            closeModal() {
                this.showModal = false;
            }
        }
    }
</script>
@endsection
