<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDataTitle extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'title_remediation',
        'sku_rem',
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
