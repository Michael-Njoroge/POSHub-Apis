<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];
    protected $table = 'pos_blogs';

    public function tags()
    {
        return $this->morphedByMany(Tag::class, 'taggable', 'pos_taggables', 'taggable_id', 'tag_id');
    }

    public function likedBy()
    {
        return $this->belongsToMany(User::class, "blog_likes");
    }

    public function dislikedBy()
    {
        return $this->belongsToMany(User::class, "blog_dislikes");
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'medially');
    }

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'category');
    }
}
