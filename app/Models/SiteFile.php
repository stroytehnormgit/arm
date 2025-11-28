<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteFile extends Model
{
    protected $table = 'site_files';
    
    protected $fillable = [
        'placement_date',
        'file_type',
        'development_name',
    ];

    protected $casts = [
        'placement_date' => 'date',
    ];
}
