<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    use HasFactory,HasUuids;

    protected $guarded = [];
    protected $table = 'pos_colors';

    public function products()
    {
        return $this->belongsToMany(Products::class, 'pos_product_color');
    }
}
