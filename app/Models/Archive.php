<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Archive extends Model
{
    protected $table = 'archives';
    
    protected $fillable = [
        'code',
        'name',
        'total_cost',
        'cost_2023',
        'cost_2024',
        'start_date',
        'end_date',
        'organization',
        'type',
        'year_period',
    ];

    protected $casts = [
        'total_cost' => 'decimal:2',
        'cost_2023' => 'decimal:2',
        'cost_2024' => 'decimal:2',
    ];
}