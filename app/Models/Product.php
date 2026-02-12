<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'price',
        'stock',
        'description',
        'image'
    ];

    public function category()
    {
        // Produk ini milik satu kategori
        return $this->belongsTo(Category::class);
    }
}
