<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'username',
        'info',
        'address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
