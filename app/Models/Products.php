<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Products extends Model
{
    use HasFactory,HasUuids;

    protected $guarded = [];
    protected $table = 'pos_products';

    public function tags()
    {
        return $this->morphedByMany(Tag::class, 'taggable', 'pos_taggables', 'taggable_id', 'tag_id');
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function colors()
    {
        return $this->belongsToMany(Color::class, 'pos_product_color');
    }
    
    public function ratings()
    {
        return $this->hasMany(Rating::class, 'product_id');
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'medially');
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function warehouse_quantities()
    {
        return $this->belongsToMany(Warehouse::class, 'pos_product_warehouse', 'product_id', 'warehouse_id')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function carts()
    {
        return $this->belongsToMany(Cart::class, 'cart_product')
                    ->withPivot('quantity', 'color', 'price')
                    ->withTimestamps();
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_products', 'order_id', 'product_id')
                    ->withPivot('count', 'color', 'price')
                    ->withTimestamps();
    }
    
    public function status()
    {
        return $this->belongsTo(ProductStatus::class, 'book_status');
    }

}
