@extends('pos.layout')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Pengaturan Toko & POS</h2>
            <p class="text-slate-500 text-sm mt-1">Ubah identitas toko, info struk, dan pengaturan metode pembayaran</p>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl mb-6 border border-emerald-200 flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    <form action="{{ route('setting.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="settingApp()">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Pengaturan Struk & Toko -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i data-lucide="store" class="w-5 h-5 text-blue-500"></i> Info Toko & Struk
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Toko</label>
                        <input type="text" name="nama_toko" value="{{ old('nama_toko', $setting->nama_toko) }}" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm" required>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Alamat Toko</label>
                        <textarea name="alamat_toko" rows="2" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm" required>{{ old('alamat_toko', $setting->alamat_toko) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">No. Telepon</label>
                        <input type="text" name="no_telp" value="{{ old('no_telp', $setting->no_telp) }}" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm" required>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Deskripsi Bawah Struk (Footer)</label>
                        <textarea name="footer_struk" rows="2" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm" required>{{ old('footer_struk', $setting->footer_struk) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Logo Toko (Opsional)</label>
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 bg-slate-100 border border-slate-200 rounded-lg flex items-center justify-center overflow-hidden shrink-0 relative">
                                <template x-if="logoPreview">
                                    <img :src="logoPreview" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!logoPreview">
                                    @if($setting->logo)
                                        <img src="{{ asset('storage/' . $setting->logo) }}" class="w-full h-full object-cover">
                                    @else
                                        <i data-lucide="image" class="w-6 h-6 text-slate-400"></i>
                                    @endif
                                </template>
                            </div>
                            <input type="file" name="logo" @change="previewLogo" accept="image/*" class="text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pengaturan Pembayaran -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i data-lucide="credit-card" class="w-5 h-5 text-blue-500"></i> Informasi Pembayaran
                </h3>
                
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Bank (Transfer)</label>
                            <input type="text" name="bank_nama" value="{{ old('bank_nama', $setting->bank_nama) }}" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm" placeholder="Contoh: BCA" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">No. Rekening</label>
                            <input type="text" name="bank_rekening" value="{{ old('bank_rekening', $setting->bank_rekening) }}" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Atas Nama Rekening</label>
                        <input type="text" name="bank_atas_nama" value="{{ old('bank_atas_nama', $setting->bank_atas_nama) }}" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm" required>
                    </div>

                    <div class="border-t border-slate-100 pt-4 mt-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Gambar QRIS (Barcode)</label>
                        <p class="text-xs text-slate-500 mb-3">Unggah gambar kode QRIS asli Anda agar pelanggan bisa langsung *scan* dari layar kasir.</p>
                        
                        <div class="flex items-start gap-4">
                            <div class="w-32 h-32 bg-slate-100 border border-slate-200 rounded-xl flex items-center justify-center overflow-hidden shrink-0 relative">
                                <template x-if="qrisPreview">
                                    <img :src="qrisPreview" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!qrisPreview">
                                    @if($setting->qris_image)
                                        <img src="{{ asset('storage/' . $setting->qris_image) }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="text-center">
                                            <i data-lucide="qr-code" class="w-8 h-8 text-slate-400 mx-auto"></i>
                                            <span class="text-[10px] text-slate-400 mt-1 block">Belum ada QRIS</span>
                                        </div>
                                    @endif
                                </template>
                            </div>
                            <div class="flex-1">
                                <input type="file" name="qris_image" @change="previewQris" accept="image/*" class="text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 w-full mb-2">
                                <p class="text-[11px] text-slate-400 leading-tight">Format: JPG/PNG, Maks. 2MB. Gambar sebaiknya berbentuk kotak (*square*).</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex justify-end">
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg shadow-blue-600/30 transition flex items-center gap-2 text-sm">
                <i data-lucide="save" class="w-4 h-4"></i> Simpan Pengaturan
            </button>
        </div>
    </form>
</div>

<script>
    function settingApp() {
        return {
            logoPreview: null,
            qrisPreview: null,
            
            previewLogo(event) {
                const file = event.target.files[0];
                if (file) {
                    this.logoPreview = URL.createObjectURL(file);
                }
            },
            
            previewQris(event) {
                const file = event.target.files[0];
                if (file) {
                    this.qrisPreview = URL.createObjectURL(file);
                }
            }
        }
    }
</script>
@endsection
