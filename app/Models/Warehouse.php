<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory,HasUuids;
    protected $guarded = [];
    protected $table = 'pos_warehouses';

    public function user()
    {
        return $this->hasMany(User::class, 'warehouse_id');
    }

    public function products()
    {
        return $this->belongsToMany(Products::class, 'pos_product_warehouse', 'warehouse_id', 'product_id')->withPivot('quantity')->withTimestamps();
    }
    
}
