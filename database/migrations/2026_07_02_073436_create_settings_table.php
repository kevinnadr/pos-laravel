<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('nama_toko')->default('Raket Murah Jogja');
            $table->string('logo')->nullable();
            $table->string('alamat_toko')->default('Jl. Olahraga No. 88, Bandung');
            $table->string('no_telp')->default('081234567890');
            $table->string('footer_struk')->default('Terima kasih atas kunjungan Anda!');
            $table->string('bank_nama')->default('BCA');
            $table->string('bank_rekening')->default('4521 8899 0012');
            $table->string('bank_atas_nama')->default('Raket Murah Jogja');
            $table->string('qris_image')->nullable();
            $table->timestamps();
        });

        // Insert default setting
        DB::table('settings')->insert([
            'nama_toko' => 'Raket Murah Jogja',
            'alamat_toko' => 'Jl. Olahraga No. 88, Bandung',
            'no_telp' => '081234567890',
            'footer_struk' => 'Terima kasih atas kunjungan Anda!',
            'bank_nama' => 'BCA',
            'bank_rekening' => '4521 8899 0012',
            'bank_atas_nama' => 'Raket Murah Jogja',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
