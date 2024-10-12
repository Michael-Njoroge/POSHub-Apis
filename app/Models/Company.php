<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory,HasUuids;
    protected $guarded = [];
    protected $table = 'pos_companies';

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

}
