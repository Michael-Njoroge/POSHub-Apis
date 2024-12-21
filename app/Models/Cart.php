<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory,HasUuids;

    protected $guarded = [];
    protected $table = 'pos_carts';

    public function products()
    {
        return $this->belongsToMany(Products::class, 'cart_product')
                    ->withPivot('quantity', 'color', 'price')
                    ->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
