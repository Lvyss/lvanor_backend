<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Weblist extends Model
{
    use HasFactory;
protected $table = 'weblist';

    protected $fillable = [
        'title',
        'image_path',
        'category_id',
        'user_id',
    ];


 // Relasi ke Detail
    public function weblistDetail()
    {
        return $this->hasOne(WebListDetail::class);
    }

    // Relasi ke Gambar Carousel
    public function weblistImages()
    {
        return $this->hasMany(WebListImage::class);
    }

    // Relasi ke Kategori
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}