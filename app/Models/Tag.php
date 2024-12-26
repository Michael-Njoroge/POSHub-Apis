<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $table = 'pos_tags';

    public function taggables()
    {
        return $this->morphToMany(self::class, 'taggable', 'pos_taggables', 'tag_id', 'taggable_id');
    }
}
