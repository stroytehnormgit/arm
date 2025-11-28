<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadedFile extends Model
{
    protected $table = 'uploaded_files';
    
    protected $fillable = [
        'date',
        'type',
        'name',
        'purpose',
        'author',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
