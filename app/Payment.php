<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'transaction_id',
        'user_id',
        'courses',
        'status',
    ];

    protected $hidden = [
        'transaction_id',
    ];

    protected $casts = [
        'courses' => 'array',
    ];
}
