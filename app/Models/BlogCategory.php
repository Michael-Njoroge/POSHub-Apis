<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogCategory extends Model
{
    use HasFactory,HasUuids;

    protected $guarded = [];
    protected $table = 'pos_blog_categories';

    public function blog()
    {
        return $this->hasMany(Blog::class);
    }
}
