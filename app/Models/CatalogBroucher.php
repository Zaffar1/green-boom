<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatalogBroucher extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'file',
        'file_type',
        'status'
    ];
}
