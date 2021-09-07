<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    public $incrementing = false;
    
    protected $fillable = [
        'id', 'user_id', 'cart_data',
    ];

    protected $casts = [
        'cart_data' => 'array',
    ];
}
