<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Item extends Model
{
    protected $primaryKey = 'itemId';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'itemId',
        'user_id',
        'image',
        'item_name',
        'item_info',
        'status',
        'qr_url'
    ];

    protected static function booted()
    {
        static::creating(function ($item) {
            if (!$item->itemId) {
                $item->itemId = (string) Str::ulid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
