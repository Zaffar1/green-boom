<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_name',
        'usage',
        'title',
        'description',
        'file',
        'status'
    ];

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function productDataDimension()
    {
        return $this->hasMany(ProductDataDimension::class);
    }

    public function productDataSize()
    {
        return $this->hasMany(ProductDataSize::class);
    }

    public function productDataTitle()
    {
        return $this->hasMany(ProductDataTitle::class);
    }
}
