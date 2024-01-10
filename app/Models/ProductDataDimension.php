<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDataDimension extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'product_dimensions(LHW)1',
        'product_dimensions(LHW)2',
        'packaging_dimensions(LHW)1',
        'packaging_dimensions(LHW)2',
        'weight_product',
        'total_weight_product',
        'product_data_size_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productDataSize()
    {
        return $this->belongsTo(ProductDataSize::class);
    }
}
