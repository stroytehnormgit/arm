<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MvsFile extends Model
{
    protected $table = 'mvs_files';
    
    protected $fillable = [
        'placement_date',
        'development_name',
    ];

    protected $casts = [
        'placement_date' => 'date',
    ];
}
