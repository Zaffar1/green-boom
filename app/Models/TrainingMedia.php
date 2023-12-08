<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingMedia extends Model
{
    use HasFactory;
    protected $fillable = [
        'training_id',
        'title',
        'file',
        'file_type',
        'status'
    ];

    public function training()
    {
        return $this->belongsTo(Training::class);
    }
}
