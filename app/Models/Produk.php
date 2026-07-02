<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode', 'nama', 'kategori_id', 'harga', 'harga_modal', 'stok', 'min_stok', 'foto'
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }
}
