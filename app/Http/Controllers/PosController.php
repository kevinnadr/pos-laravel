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
        
        $transaksiTerbaru = \App\Models\Transaksi::latest()->take(5)->get();

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
        $kategoris = \App\Models\Kategori::withCount('produks')->get();
        return view('pos.kategori', compact('kategoris'));
    }

    public function storeKategori(\Illuminate\Http\Request $request)
    {
        $request->validate(['nama' => 'required']);
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
        $kategori->delete();
        return redirect()->back()->with('success', 'Kategori berhasil dihapus.');
    }

    public function produk(Request $request)
    {
        $kategori_id = $request->query('kategori');
        $query = \App\Models\Produk::with('kategori');
        
        if ($kategori_id) {
            $query->where('kategori_id', $kategori_id);
        }
        
        $produks = $query->get();
        $kategoris = \App\Models\Kategori::all();
        return view('pos.produk', compact('produks', 'kategoris', 'kategori_id'));
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
        $produk = \App\Models\Produk::findOrFail($id);
        
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
        $kategoris = Kategori::all();
        $produks = Produk::where('stok', '>', 0)->get();
        return view('pos.kasir', compact('kategoris', 'produks'));
    }

    public function riwayat()
    {
        $transaksis = Transaksi::with('items')->orderBy('created_at', 'desc')->get();
        return view('pos.riwayat', compact('transaksis'));
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
            'kasir' => 'Administrator', // Mock user for now
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

        return response()->json(['success' => true, 'transaksi_id' => $transaksi->id]);
    }
}
