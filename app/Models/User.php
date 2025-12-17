<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'firstname',
        'lastname',
        'name',
        'email',
        'password',
        'profile_image',
        'role',
    ];

    protected $hidden = [
        'firstname',
        'lastname',
        'name',
        'email',
        'password',
        'profile_image',
        'role',
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    // Relationships
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    // Accessor for avatar
    protected $appends = ['profile_image_url'];

    public function getProfileImageUrlAttribute()
    {
        return $this->profile_image
            ? asset('storage/' . $this->profile_image)
            : asset('defaults/avatar.png');
    }
}
