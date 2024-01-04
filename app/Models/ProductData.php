<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductData extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'sku_num',
        'size',
        'dimensions',
        'Absorbency',
        'qty',
        'case',
        'added_remediation_material',
        'product_dimensions_size',
        'product_dimensions_cm',
        'packaging_dimensions_size',
        'packaging_dimensions_cm',
        'weight',
        'product',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
