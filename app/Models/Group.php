<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory,HasUuids;

    protected $guarded = [];
    protected $table = 'pos_groups';

    public function users()
    {
        return $this->hasMany(User::class, 'group_id');
    }

    public function companies()
    {
        return $this->hasMany(Company::class, 'group_id');
    }
}
