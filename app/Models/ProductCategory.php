<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory,HasUuids;

    protected $guarded = [];
    protected $table = 'pos_products_category';    

    public function products()
    {
        return $this->hasMany(Products::class);
    }
    
}
