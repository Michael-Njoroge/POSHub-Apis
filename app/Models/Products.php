<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory,HasUuids;

    protected $guarded = [];
    protected $table = 'pos_products';

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }
    
    public function warehouse_quantities()
    {
        return $this->belongsToMany(Warehouse::class, 'product_warehouse', 'product_id', 'warehouse_id')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }
    
    public function status()
    {
        return $this->belongsTo(ProductStatus::class, 'book_status');
    }

}
