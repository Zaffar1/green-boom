<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderKitVideo extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'file',
        'order_kit_id',
        'status',
    ];

    public function orderKit()
    {
        return $this->belongsTo(OrderKit::class);
    }
}
