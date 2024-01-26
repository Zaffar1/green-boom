<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderKit extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'short_description',
        'description',
        'kit_includes',
        'image',
        'file',
        'status',
    ];

    public function orderKitForms()
    {
        return $this->hasMany(OrderKitForm::class, 'order_kit_id');
    }
}
