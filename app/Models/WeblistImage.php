<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebListImage extends Model
{
    use HasFactory;

    protected $table = 'weblist_images';

    protected $fillable = [
        'weblist_id',
        'image_path',
        'public_id',
    ];

    // Relasi ke WebList
    public function weblist()
    {
        return $this->belongsTo(Weblist::class);
    }
}
