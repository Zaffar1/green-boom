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
}
