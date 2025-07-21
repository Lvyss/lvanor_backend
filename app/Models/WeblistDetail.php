<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebListDetail extends Model
{
    use HasFactory;

    protected $table = 'weblist_detail';

    protected $fillable = [
        'weblist_id',
        'description',
        'features',
        'tech_stack',
        'price',
        'website_link',
    ];

    protected $casts = [
        'features' => 'array', // Kalau pakai JSON, biar otomatis array
    ];

    // Relasi ke WebList
    public function weblist()
    {
        return $this->belongsTo(Weblist::class);
    }
}
