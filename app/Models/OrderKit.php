<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderKit extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'short_description',
        'description',
        'kit_includes',
        'image',
        'status',
    ];

    public function videos()
    {
        return $this->hasMany(OrderKitVideo::class);
    }
}
