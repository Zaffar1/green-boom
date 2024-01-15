<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDescription extends Model
{
    use HasFactory;
    protected $fillable = [
        'sub_description',
        'product_data_size_id',
    ];

    public function productDataSize()
    {
        return $this->belongsTo(ProductDataSize::class);
    }
}
