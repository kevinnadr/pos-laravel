<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@raketmurahjogja.id',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'status' => 'aktif'
        ]);

        User::create([
            'name' => 'Budi Santoso',
            'username' => 'budi',
            'email' => 'budi@raketmurahjogja.id',
            'password' => Hash::make('kasir123'),
            'role' => 'kasir',
            'status' => 'aktif'
        ]);

        $kategoris = [
            ['nama' => 'Raket', 'deskripsi' => 'Raket badminton semua merek dan level'],
            ['nama' => 'Shuttlecock', 'deskripsi' => 'Kok bulu dan plastik berbagai kecepatan'],
            ['nama' => 'Sepatu', 'deskripsi' => 'Sepatu badminton khusus lapangan indoor'],
            ['nama' => 'Tas', 'deskripsi' => 'Tas raket dan tas olahraga badminton'],
            ['nama' => 'Pakaian', 'deskripsi' => 'Jersey, kaos, dan celana olahraga'],
            ['nama' => 'Aksesori', 'deskripsi' => 'Grip, string, kaos kaki, dan aksesoris lain'],
        ];

        foreach ($kategoris as $k) {
            Kategori::create($k);
        }

        $produks = [
            ['kode' => 'RKT001', 'nama' => 'Yonex Astrox 99 Pro', 'kategori_id' => 1, 'harga' => 3200000, 'harga_modal' => 2400000, 'stok' => 8, 'min_stok' => 3],
            ['kode' => 'RKT002', 'nama' => 'Li-Ning Turbocharging N9 II', 'kategori_id' => 1, 'harga' => 1850000, 'harga_modal' => 1400000, 'stok' => 12, 'min_stok' => 3],
            ['kode' => 'KOK001', 'nama' => 'Yonex Aerosensa 40', 'kategori_id' => 2, 'harga' => 120000, 'harga_modal' => 85000, 'stok' => 50, 'min_stok' => 10],
            ['kode' => 'SPT001', 'nama' => 'Yonex Power Cushion 65Z3', 'kategori_id' => 3, 'harga' => 1650000, 'harga_modal' => 1200000, 'stok' => 10, 'min_stok' => 3],
        ];

        foreach ($produks as $p) {
            Produk::create($p);
        }
    }
}
