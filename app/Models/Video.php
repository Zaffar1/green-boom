<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'file',
        'status',
        'video_cat_id',
        'thumbnail'
    ];


    public function videos()
    {
        return $this->belongsTo(VideoCategory::class);
    }
}
