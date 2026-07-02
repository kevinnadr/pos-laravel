@extends('pos.layout')

@section('scripts')
@endsection

@section('content')
<div class="p-6" x-data="akunApp()">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Manajemen Akun</h1>
            <p class="text-slate-500 text-sm mt-1">{{ count($akuns) }} akun terdaftar</p>
        </div>
        <button @click="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium flex items-center gap-2 transition shadow-sm">
            <i data-lucide="plus" class="w-4 h-4"></i> Tambah Akun
        </button>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-emerald-50 text-emerald-600 p-3 rounded-lg text-sm flex items-center gap-2 border border-emerald-200">
        <i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}
    </div>
    @endif
    
    @if($errors->any())
    <div class="mb-4 bg-red-50 text-red-600 p-3 rounded-lg text-sm flex items-center gap-2 border border-red-200">
        <i data-lucide="alert-circle" class="w-4 h-4"></i> {{ $errors->first() }}
    </div>
    @endif

    <div class="mb-6 bg-white p-3 rounded-xl shadow-sm border border-slate-200 flex gap-4">
        <div class="relative flex-1">
            <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" x-model="search" placeholder="Cari nama / username..." class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <template x-for="akun in filteredAkuns" :key="akun.id">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex flex-col hover:border-blue-200 transition">
                <div class="flex items-start gap-4 mb-4">
                    <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-lg shrink-0">
                        <span x-text="(akun.name || '').charAt(0).toUpperCase()"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-slate-800 text-lg truncate" x-text="akun.name"></h3>
                        <p class="text-sm text-slate-500 truncate" x-text="'@' + akun.username"></p>
                        
                        <div class="mt-2 flex gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-600 border border-blue-100" x-text="akun.role"></span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider" 
                                :class="akun.status === 'aktif' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-red-50 text-red-600 border border-red-100'"
                                x-text="akun.status === 'aktif' ? 'Aktif' : 'Nonaktif'"></span>
                        </div>
                    </div>
                </div>
                
                <div class="mt-auto pt-4 border-t border-slate-100 flex justify-end gap-2">
                    <button @click="openModal(akun)" class="text-slate-500 hover:text-blue-600 px-3 py-1.5 border border-slate-200 rounded-md text-sm font-medium hover:bg-slate-50 flex items-center gap-1 transition">
                        <i data-lucide="edit-2" class="w-3.5 h-3.5"></i> Edit
                    </button>
                    
                    <form :action="`/akun/${akun.id}`" method="POST" class="inline" onsubmit="return confirm('Hapus akun ini?');" x-show="akun.id !== {{ auth()->id() }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-600 px-3 py-1.5 border border-red-100 bg-red-50 rounded-md text-sm font-medium hover:bg-red-100 flex items-center gap-1 transition">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Hapus
                        </button>
                    </form>
                </div>
            </div>
        </template>
        <div x-show="filteredAkuns.length === 0" class="col-span-full py-12 text-center text-slate-500">
            Tidak ada akun ditemukan.
        </div>
    </div>

    <!-- Modal Form Akun -->
    <div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 bg-slate-900/50 flex items-center justify-center p-4">
        <div @click.away="closeModal()" class="bg-white w-full max-w-lg rounded-2xl shadow-xl overflow-hidden transform transition-all">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-bold text-lg text-slate-800" x-text="isEdit ? 'Edit Akun' : 'Tambah Akun'"></h3>
                <button @click="closeModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            
            <form :action="isEdit ? `/akun/${form.id}` : '{{ route('akun.store') }}'" method="POST">
                @csrf
                <template x-if="isEdit">
                    @method('PUT')
                </template>
                
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Nama Lengkap</label>
                        <input type="text" name="nama" x-model="form.nama" required placeholder="cth. Budi Santoso" class="w-full px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Role</label>
                            <select name="role" x-model="form.role" required class="w-full px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                <option value="kasir">Kasir</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Status</label>
                            <select name="status" x-model="form.status" required class="w-full px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Username</label>
                        <input type="text" name="username" x-model="form.username" required placeholder="username_login" class="w-full px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Email</label>
                        <input type="email" name="email" x-model="form.email" required placeholder="nama@raketmurahjogja.id" class="w-full px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Password <span x-show="isEdit" class="text-slate-400 normal-case font-normal">(Kosongkan jika tidak ingin mengubah)</span></label>
                        <input type="text" name="password" :required="!isEdit" placeholder="Min. 6 karakter" minlength="6" class="w-full px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" @click="closeModal()" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm shadow-blue-600/20" x-text="isEdit ? 'Simpan Perubahan' : 'Simpan Akun'"></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function akunApp() {
        return {
            akuns: @json($akuns),
            search: '',
            showModal: false,
            isEdit: false,
            form: {
                id: null,
                nama: '',
                username: '',
                email: '',
                role: 'kasir',
                status: 'aktif'
            },
            
            get filteredAkuns() {
                if (this.search === '') return this.akuns;
                const searchLower = this.search.toLowerCase();
                return this.akuns.filter(a => 
                    (a.name || '').toLowerCase().includes(searchLower) || 
                    (a.username || '').toLowerCase().includes(searchLower)
                );
            },
            
            openModal(akun = null) {
                if (akun) {
                    this.isEdit = true;
                    this.form.id = akun.id;
                    this.form.nama = akun.name;
                    this.form.username = akun.username;
                    this.form.email = akun.email;
                    this.form.role = akun.role;
                    this.form.status = akun.status;
                } else {
                    this.isEdit = false;
                    this.form.id = null;
                    this.form.nama = '';
                    this.form.username = '';
                    this.form.email = '';
                    this.form.role = 'kasir';
                    this.form.status = 'aktif';
                }
                this.showModal = true;
                setTimeout(() => lucide.createIcons(), 50);
            },
            
            closeModal() {
                this.showModal = false;
            }
        }
    }
</script>
@endsection
