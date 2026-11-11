<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\TransaksiItem;

class PosController extends Controller
{
    public function dashboard()
    {
        $kasirAktif = \App\Models\User::where('role', 'kasir')->where('status', 'aktif')->count();
        $totalKasir = \App\Models\User::where('role', 'kasir')->count();
        
        $totalProduk = \App\Models\Produk::count();
        $totalUnitProduk = \App\Models\Produk::sum('stok');
        
        $transaksiHariIni = \App\Models\Transaksi::whereDate('created_at', \Carbon\Carbon::today())->count();
        $pendapatanHariIni = \App\Models\Transaksi::whereDate('created_at', \Carbon\Carbon::today())->sum('total');
        
        $stokMenipisList = \App\Models\Produk::whereColumn('stok', '<=', 'min_stok')->orderBy('stok', 'asc')->take(4)->get();
        $stokMenipisCount = \App\Models\Produk::whereColumn('stok', '<=', 'min_stok')->count();
        
        $transaksiTerbaru = \App\Models\Transaksi::whereDate(
            'created_at',
            \Carbon\Carbon::today()
        )->latest()->paginate(15);

        // Data Penjualan 7 Hari Terakhir
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = \Carbon\Carbon::today()->subDays($i);
            $sum = \App\Models\Transaksi::whereDate('created_at', $date)->sum('total');
            $chartData[$date->isoFormat('ddd')] = $sum;
        }

        return view('pos.dashboard', compact(
            'kasirAktif', 'totalKasir', 'totalProduk', 'totalUnitProduk',
            'transaksiHariIni', 'pendapatanHariIni', 'stokMenipisList',
            'stokMenipisCount', 'transaksiTerbaru', 'chartData'
        ));
    }

    public function kategori()
    {
        $kategoris = \App\Models\Kategori::withCount('produks')
            ->orderBy('nama')
            ->get();
        return view('pos.kategori', compact('kategoris'));
    }

    public function storeKategori(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
        ]);
        \App\Models\Kategori::create($request->only(['nama', 'deskripsi']));
        return redirect()->back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function updateKategori(\Illuminate\Http\Request $request, $id)
    {
        $request->validate(['nama' => 'required']);
        $kategori = \App\Models\Kategori::findOrFail($id);
        $kategori->update($request->only(['nama', 'deskripsi']));
        return redirect()->back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroyKategori($id)
    {
        $kategori = \App\Models\Kategori::findOrFail($id);

        if ($kategori->produks()->exists()) {
            return redirect()->back()->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh produk.');
        }

        $kategori->delete();
        return redirect()->back()->with('success', 'Kategori berhasil dihapus.');
    }

    public function produk(Request $request)
    {
        $kategori_id = $request->query('kategori');
        $editProdukId = $request->validate([
            'edit' => ['nullable', 'integer', 'exists:produks,id'],
        ])['edit'] ?? null;
        $query = \App\Models\Produk::with('kategori');
        
        if ($kategori_id) {
            $query->where('kategori_id', $kategori_id);
        }
        
        $produks = $query->orderBy('nama')->get();
        $kategoris = \App\Models\Kategori::withCount('produks')->orderBy('nama')->get();
        return view('pos.produk', compact('produks', 'kategoris', 'kategori_id', 'editProdukId'));
    }

    public function storeProduk(Request $request)
    {
        $request->validate([
            'kode' => 'required|unique:produks',
            'nama' => 'required',
            'kategori_id' => 'required|exists:kategoris,id',
            'harga' => 'required|numeric',
            'harga_modal' => 'required|numeric',
            'stok' => 'required|numeric',
            'min_stok' => 'required|numeric',
            'foto' => 'nullable|image|max:2048'
        ]);

        $data = $request->except('foto');
        
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('produks', 'public');
            $data['foto'] = $path;
        }

        \App\Models\Produk::create($data);
        return redirect()->back()->with('success', 'Produk berhasil ditambahkan.');
    }

    public function updateProduk(Request $request, $id)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $id) {
            $produk = \App\Models\Produk::whereKey($id)->lockForUpdate()->firstOrFail();

            if ($request->user()->role === 'kasir') {
                $data = $request->validate([
                    'stok_tambah' => ['required', 'integer', 'min:1'],
                ], [
                    'stok_tambah.required' => 'Masukkan jumlah stok yang ingin ditambahkan.',
                    'stok_tambah.integer' => 'Jumlah stok harus berupa bilangan bulat.',
                    'stok_tambah.min' => 'Jumlah stok yang ditambahkan minimal 1.',
                ]);

                $produk->increment('stok', $data['stok_tambah']);

                return redirect()->back()->with('success', 'Stok produk berhasil ditambahkan.');
            }

            $request->validate([
                'kode' => 'required|unique:produks,kode,'.$id,
                'nama' => 'required',
                'kategori_id' => 'required|exists:kategoris,id',
                'harga' => 'required|numeric',
                'harga_modal' => 'required|numeric',
                'stok' => 'required|numeric',
                'min_stok' => 'required|numeric',
                'foto' => 'nullable|image|max:2048'
            ]);

            $data = $request->except('foto');
            
            if ($request->hasFile('foto')) {
                if ($produk->foto) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($produk->foto);
                }
                $path = $request->file('foto')->store('produks', 'public');
                $data['foto'] = $path;
            }

            $produk->update($data);
            return redirect()->back()->with('success', 'Produk berhasil diperbarui.');
        });
    }

    public function destroyProduk($id)
    {
        $produk = \App\Models\Produk::findOrFail($id);
        if ($produk->foto) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($produk->foto);
        }
        $produk->delete();
        return redirect()->back()->with('success', 'Produk berhasil dihapus.');
    }

    public function pos()
    {
        $kategoris = Kategori::orderBy('nama')->get();
        $produks = Produk::where('stok', '>', 0)
            ->orderBy('nama')
            ->get();
        return view('pos.kasir', compact('kategoris', 'produks'));
    }

    public function riwayat(Request $request)
    {
        $modeTanggal = $request->query('mode_tanggal', 'rentang');
        $filters = $request->validate([
            'mode_tanggal' => ['sometimes', 'in:tanggal,rentang'],
            'tanggal' => ['nullable', 'required_if:mode_tanggal,tanggal', 'date_format:Y-m-d'],
            'tanggal_mulai' => ['nullable', 'date_format:Y-m-d'],
            'tanggal_selesai' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:tanggal_mulai'],
        ], [
            'tanggal.required_if' => 'Pilih tanggal yang ingin dicari.',
            'tanggal.date_format' => 'Format tanggal tidak valid.',
            'tanggal_mulai.date_format' => 'Format tanggal mulai tidak valid.',
            'tanggal_selesai.date_format' => 'Format tanggal selesai tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        ]);

        $query = Transaksi::with('items');

        if ($modeTanggal === 'tanggal' && !empty($filters['tanggal'])) {
            $query->whereDate('created_at', $filters['tanggal']);
        } elseif ($modeTanggal === 'rentang') {
            if (!empty($filters['tanggal_mulai'])) {
                $query->whereDate('created_at', '>=', $filters['tanggal_mulai']);
            }

            if (!empty($filters['tanggal_selesai'])) {
                $query->whereDate('created_at', '<=', $filters['tanggal_selesai']);
            }
        }

        $transaksis = $query->orderBy('created_at', 'desc')->get();
        $tanggal = $filters['tanggal'] ?? '';
        $tanggalMulai = $filters['tanggal_mulai'] ?? '';
        $tanggalSelesai = $filters['tanggal_selesai'] ?? '';

        return view('pos.riwayat', compact(
            'transaksis',
            'modeTanggal',
            'tanggal',
            'tanggalMulai',
            'tanggalSelesai'
        ));
    }

    public function storeTransaksi(Request $request)
    {
        $data = $request->validate([
            'subtotal' => 'required|integer',
            'diskon' => 'required|integer',
            'total' => 'required|integer',
            'metode' => 'required|string',
            'bayar' => 'required|integer',
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.qty' => 'required|integer',
        ]);

        $transaksiId = 'TRX-' . date('ymd') . '-' . rand(1000, 9999);

        $transaksi = Transaksi::create([
            'id' => $transaksiId,
            'user_id' => $request->user()->id,
            'kasir' => $request->user()->name,
            'subtotal' => $data['subtotal'],
            'diskon' => $data['diskon'],
            'total' => $data['total'],
            'metode' => $data['metode'],
            'bayar' => $data['bayar'],
        ]);

        foreach ($data['items'] as $item) {
            $produk = Produk::find($item['id']);
            if ($produk) {
                TransaksiItem::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produk->id,
                    'nama_produk' => $produk->nama,
                    'harga' => $produk->harga,
                    'harga_modal' => $produk->harga_modal,
                    'qty' => $item['qty'],
                ]);
                
                // Reduce stock
                $produk->decrement('stok', $item['qty']);
            }
        }

        return response()->json([
            'success' => true,
            'transaksi_id' => $transaksi->id,
            'kasir' => $transaksi->kasir,
            'kembalian' => max(0, (int) $transaksi->bayar - (int) $transaksi->total),
        ]);
    }
}
