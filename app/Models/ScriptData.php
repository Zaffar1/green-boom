<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScriptData extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'file',
        'file_type',
        'perfect_sale_media_id',
        'status'
    ];

    public function scriptData()
    {
        return $this->belongsTo(PerfectSaleMedia::class);
    }
}
