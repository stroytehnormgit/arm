<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $table = 'reports';
    
    protected $fillable = [
        'punkt',
        'etap',
        'nazvanie',
        'razrabotchik',
        'stoimost',
        'period',
    ];

    protected $casts = [
        'stoimost' => 'decimal:2',
    ];
}