<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Item extends Model
{
    protected $primaryKey = "itemId";
    public $incrementing = false;
    protected $keyType = "string";

    protected $fillable = [
        "itemId",
        "user_id",
        "image",
        "item_name",
        "item_info",
        "status",
        "qr_url",
        "last_seen_at",
    ];

    protected static function booted()
    {
        static::creating(function ($Item) {
            if (!$Item->itemId) {
                $Item->itemId = (string) Str::ulid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, "user_id", "userId");
    }

    public function last_seen_location()
    {
        return $this->hasOne(
            ItemLocation::class,
            "item_id",
            "itemId",
        )->latestOfMany("locationId");
    }
}
