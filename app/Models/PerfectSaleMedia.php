<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerfectSaleMedia extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'file',
        'file_type',
        'perfect_sale_id',
        'thumbnail',
        'status'
    ];

    public function perfectSale()
    {
        return $this->belongsTo(PerfectSale::class);
    }

    public function scriptMedia()
    {
        return $this->hasMany(ScriptData::class);
    }
}
