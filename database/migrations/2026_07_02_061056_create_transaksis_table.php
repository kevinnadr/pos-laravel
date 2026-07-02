<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaksis', function (Blueprint $table) {
            $table->string('id')->primary(); // TRX-240630-001
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('kasir'); // Snapshot nama kasir
            $table->integer('subtotal');
            $table->integer('diskon')->default(0);
            $table->integer('total');
            $table->enum('metode', ['cash', 'qris', 'transfer']);
            $table->integer('bayar');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
