<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    use HasFactory;

    // Kolom yang boleh diisi mass-assignment
    protected $fillable = [
        'user_id',
        'token',
    ];

    /**
     * Relasi balik ke model User
     * (Setiap token ini dimiliki oleh satu User)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


