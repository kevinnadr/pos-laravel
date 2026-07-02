<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\TransaksiItem;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'minggu');
        $query = Transaksi::with('items');

        if ($filter === 'hari') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($filter === 'minggu') {
            $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($filter === 'bulan') {
            $query->whereMonth('created_at', Carbon::now()->month)
                  ->whereYear('created_at', Carbon::now()->year);
        }

        $transaksis = $query->orderBy('created_at', 'desc')->get();

        $totalPenjualan = $transaksis->sum('total');
        
        $totalModal = 0;
        foreach ($transaksis as $t) {
            foreach ($t->items as $item) {
                $totalModal += ($item->harga_modal * $item->qty);
            }
        }

        $labaKotor = $totalPenjualan - $totalModal;
        $marginLaba = $totalPenjualan > 0 ? ($labaKotor / $totalPenjualan) * 100 : 0;

        // Data for chart
        $chartData = [];
        
        if ($filter === 'hari') {
            // Inisialisasi 24 jam
            for ($i = 0; $i < 24; $i++) {
                $hourStr = sprintf("%02d:00", $i);
                $chartData[$hourStr] = 0;
            }

            $grouped = $transaksis->groupBy(function($date) {
                return Carbon::parse($date->created_at)->format('H:00');
            });

            foreach ($grouped as $key => $trx) {
                $chartData[$key] = $trx->sum('total');
            }
        } elseif ($filter === 'semua' || $filter === 'minggu' || $filter === 'bulan') {
            $grouped = $transaksis->groupBy(function($date) {
                return Carbon::parse($date->created_at)->format('Y-m-d');
            });
            
            foreach ($grouped as $key => $trx) {
                $chartData[$key] = $trx->sum('total');
            }
            ksort($chartData);
        }

        return view('pos.laporan', compact(
            'transaksis', 'totalPenjualan', 'totalModal', 'labaKotor', 'marginLaba', 'filter', 'chartData'
        ));
    }

    private function getFilteredQuery($filter)
    {
        $query = Transaksi::with('items');

        if ($filter === 'hari') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($filter === 'minggu') {
            $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($filter === 'bulan') {
            $query->whereMonth('created_at', Carbon::now()->month)
                  ->whereYear('created_at', Carbon::now()->year);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function exportPenjualan(Request $request)
    {
        $filter = $request->query('filter', 'minggu');
        $transaksis = $this->getFilteredQuery($filter)->get();

        $filename = "Export_Penjualan_" . Carbon::now()->format('Ymd_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($transaksis) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID Transaksi', 'Tanggal', 'Kasir', 'Total Penjualan', 'Metode']);

            $grandTotal = 0;
            foreach ($transaksis as $t) {
                $grandTotal += $t->total;
                fputcsv($file, [
                    $t->id,
                    $t->created_at->format('d/m/Y H:i'),
                    $t->kasir,
                    $t->total,
                    $t->metode
                ]);
            }
            
            // Tambahkan baris kosong sebagai pemisah, lalu baris Total
            fputcsv($file, ['', '', '', '', '']);
            fputcsv($file, ['TOTAL KESELURUHAN', '', '', $grandTotal, '']);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportKeuangan(Request $request)
    {
        $filter = $request->query('filter', 'minggu');
        $transaksis = $this->getFilteredQuery($filter)->get();

        $filename = "Export_Keuangan_" . Carbon::now()->format('Ymd_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($transaksis) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID Transaksi', 'Tanggal', 'Kasir', 'Nama Barang', 'Qty', 'Harga Jual', 'Harga Modal', 'Total Jual', 'Total Modal', 'Laba']);

            $grandTotalJual = 0;
            $grandTotalModal = 0;
            $grandLaba = 0;

            foreach ($transaksis as $t) {
                foreach ($t->items as $item) {
                    $totalJual = $item->harga * $item->qty;
                    $totalModal = $item->harga_modal * $item->qty;
                    $laba = $totalJual - $totalModal;
                    
                    $grandTotalJual += $totalJual;
                    $grandTotalModal += $totalModal;
                    $grandLaba += $laba;

                    fputcsv($file, [
                        $t->id,
                        $t->created_at->format('d/m/Y H:i'),
                        $t->kasir,
                        $item->nama_produk,
                        $item->qty,
                        $item->harga,
                        $item->harga_modal,
                        $totalJual,
                        $totalModal,
                        $laba
                    ]);
                }
            }
            
            // Tambahkan baris kosong sebagai pemisah, lalu baris Total
            fputcsv($file, ['', '', '', '', '', '', '', '', '', '']);
            fputcsv($file, ['TOTAL KESELURUHAN', '', '', '', '', '', '', $grandTotalJual, $grandTotalModal, $grandLaba]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
