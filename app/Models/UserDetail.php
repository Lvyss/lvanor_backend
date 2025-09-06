<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'profile_picture',
        'profile_public_id',
        'full_name',
        'username',
        'bio',
        'location',
        'email',
        'linkedin',
        'github',
        'website',
        'tiktok',
        'instagram',
        'spline',
        'banner_image',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
