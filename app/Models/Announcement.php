<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Announcement extends Model
{
    protected $primaryKey = 'announcementId';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'announcementId',
        'user_id',
        'item_id',
        'title',
        'description',
        'location',
        'lost_at',
        'status'
    ];

    protected static function booted()
    {
        static::creating(function ($ann) {
            if (!$ann->announcementId) {
                $ann->announcementId = (string) Str::ulid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
