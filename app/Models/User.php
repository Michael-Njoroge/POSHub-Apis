<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids,HasApiTokens, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    protected $table = 'pos_users';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function user_logins()
    {
        return $this->hasMany(UserLogin::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function wishlist()
    {
        return $this->belongsToMany(Products::class, 'user_products', 'user_id', 'product_id')
                    ->withTimestamps();;
    }

    public function likedBlogs()
    {
        return $this->belongsToMany(Blog::class,"likes");
    }

    public function dislikedBlogs()
    {
        return $this->belongsToMany(Blog::class,"dislikes");
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function inGroup(string $groupName): bool
    {
        return $this->group && $this->group->name === $groupName;
    }
}
