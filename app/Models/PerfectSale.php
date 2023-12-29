<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerfectSale extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'status'
    ];

    public function perfectSaleMedia()
    {
        return $this->belongsTo(PerfectSaleMedia::class);
    }
}
