<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $table = 'pos_brands';

    public function products()
    {
        return $this->hasMany(Products::class, 'brand');
    }
}
