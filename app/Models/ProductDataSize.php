<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDataSize extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'sku_num',
        'size',
        'dimensions',
        'absorbency_bag',
        'absorbency_drum',
        'absorbency_pillow',
        'absorbency_boom',
        'absorbency_sock',
        'absorbency_mat',
        'absorbency_kit',
        'capacity',
        'qty_case',
        'added_remediation_material',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productDataDimension()
    {
        return $this->hasMany(ProductDataDimension::class);
    }

    public function productDataTitle()
    {
        return $this->hasMany(ProductDataTitle::class);
    }

    public function productDescription()
    {
        return $this->hasMany(ProductDescription::class);
    }
}
