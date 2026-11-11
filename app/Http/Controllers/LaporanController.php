<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\TransaksiItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $request->merge(['filter' => $request->query('filter', 'minggu')]);
        $filters = $request->validate([
            'filter' => ['required', 'in:hari,minggu,bulan,semua'],
            'tanggal_mode' => ['nullable', 'in:tanggal,rentang'],
            'tanggal' => ['nullable', 'required_if:tanggal_mode,tanggal', 'date_format:Y-m-d'],
            'tanggal_mulai' => ['nullable', 'required_if:tanggal_mode,rentang', 'date_format:Y-m-d'],
            'tanggal_selesai' => ['nullable', 'required_if:tanggal_mode,rentang', 'date_format:Y-m-d', 'after_or_equal:tanggal_mulai'],
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'in:10,20,50,100'],
        ], [
            'tanggal.required_if' => 'Pilih tanggal yang ingin dicari.',
            'tanggal.date_format' => 'Format tanggal tidak valid.',
            'tanggal_mulai.required_if' => 'Pilih tanggal mulai.',
            'tanggal_mulai.date_format' => 'Format tanggal mulai tidak valid.',
            'tanggal_selesai.required_if' => 'Pilih tanggal selesai.',
            'tanggal_selesai.date_format' => 'Format tanggal selesai tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'search.max' => 'Pencarian maksimal 100 karakter.',
        ]);
        $filter = $filters['filter'];
        $perPage = (int) ($filters['per_page'] ?? 10);
        $query = $this->getFilteredQuery($filter);
        $summary = (clone $query)
            ->selectRaw('COUNT(*) as jumlah_transaksi, COALESCE(SUM(total), 0) as total_penjualan')
            ->first();
        $jumlahTransaksi = (int) $summary->jumlah_transaksi;
        $totalPenjualan = (int) $summary->total_penjualan;
        $transaksiIds = (clone $query)->select('id');
        $totalModal = (int) TransaksiItem::whereIn('transaksi_id', $transaksiIds)
            ->sum(DB::raw('harga_modal * qty'));
        $labaKotor = $totalPenjualan - $totalModal;
        $marginLaba = $totalPenjualan > 0 ? ($labaKotor / $totalPenjualan) * 100 : 0;

        $chartData = [];
        $chartTransaksis = (clone $query)
            ->get(['id', 'total', 'created_at']);

        if ($filter === 'hari') {
            for ($i = 0; $i < 24; $i++) {
                $hourStr = sprintf("%02d:00", $i);
                $chartData[$hourStr] = 0;
            }

            $grouped = $chartTransaksis->groupBy(function($date) {
                return Carbon::parse($date->created_at)->format('H:00');
            });

            foreach ($grouped as $key => $trx) {
                $chartData[$key] = $trx->sum('total');
            }
        } else {
            $grouped = $chartTransaksis->groupBy(function($date) {
                return Carbon::parse($date->created_at)->format('Y-m-d');
            });
            
            foreach ($grouped as $key => $trx) {
                $chartData[$key] = $trx->sum('total');
            }
            ksort($chartData);
        }

        $tableQuery = clone $query;
        if (($filters['tanggal_mode'] ?? null) === 'tanggal' && !empty($filters['tanggal'])) {
            $tableQuery->whereDate('created_at', $filters['tanggal']);
        } elseif (($filters['tanggal_mode'] ?? null) === 'rentang') {
            $tableQuery->whereDate('created_at', '>=', $filters['tanggal_mulai'])
                ->whereDate('created_at', '<=', $filters['tanggal_selesai']);
        }

        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $tableQuery->where(function ($query) use ($searchTerm) {
                $query->where('id', 'like', "%{$searchTerm}%")
                    ->orWhere('kasir', 'like', "%{$searchTerm}%");
            });
        }

        $transaksis = (clone $tableQuery)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();
        $transaksiHalaman = $transaksis->getCollection();
        $tanggal = $filters['tanggal'] ?? '';
        $tanggalMulai = $filters['tanggal_mulai'] ?? '';
        $tanggalSelesai = $filters['tanggal_selesai'] ?? '';
        $tanggalMode = $filters['tanggal_mode'] ?? '';
        $search = $filters['search'] ?? '';
        $filterOptions = [
            'hari' => 'Hari Ini',
            'minggu' => 'Minggu Ini',
            'bulan' => 'Bulan Ini',
            'semua' => 'Semua Waktu',
        ];
        $exportFilters = ['filter' => $filter];

        return view('pos.laporan', compact(
            'transaksis',
            'transaksiHalaman',
            'jumlahTransaksi',
            'totalPenjualan',
            'totalModal',
            'labaKotor',
            'marginLaba',
            'filter',
            'filterOptions',
            'tanggal',
            'tanggalMulai',
            'tanggalSelesai',
            'tanggalMode',
            'search',
            'perPage',
            'exportFilters',
            'chartData'
        ));
    }

    private function getFilteredQuery(string $filter)
    {
        $query = Transaksi::query();

        if ($filter === 'hari') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($filter === 'minggu') {
            $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($filter === 'bulan') {
            $query->whereMonth('created_at', Carbon::now()->month)
                  ->whereYear('created_at', Carbon::now()->year);
        }

        return $query;
    }

    public function exportPenjualan(Request $request)
    {
        $filters = $this->validateExportFilters($request);
        $transaksis = $this->getFilteredQuery($filters['filter'])
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->get();

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
        $filters = $this->validateExportFilters($request);
        $transaksis = $this->getFilteredQuery($filters['filter'])
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->get();

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

    private function validateExportFilters(Request $request): array
    {
        $request->merge(['filter' => $request->query('filter', 'minggu')]);

        return $request->validate([
            'filter' => ['required', 'in:hari,minggu,bulan,semua'],
        ]);
    }
}
