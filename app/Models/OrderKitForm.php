<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderKitForm extends Model
{
    use HasFactory;
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'company',
        'country',
        'state',
        'zip_code',
        'city',
        'order_kit_id',
        'address',
        'status'
    ];

    public function orderKit()
    {
        return $this->belongsTo(OrderKit::class, 'order_kit_id');
    }
}
