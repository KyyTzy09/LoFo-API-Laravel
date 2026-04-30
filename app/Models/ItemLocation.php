<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ItemLocation extends Model
{
    protected $primaryKey = "locationId";
    public $incrementing = false;
    protected $keyType = "string";

    protected $fillable = ["locationId", "item_id", "latitude", "longitude"];

    protected static function booted()
    {
        static::creating(function ($location) {
            if (!$location->locationId) {
                $location->locationId = (string) Str::ulid();
            }
        });
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
