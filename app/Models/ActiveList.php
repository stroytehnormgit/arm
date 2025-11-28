<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActiveList extends Model
{
    protected $table = 'active_list';
    
    protected $fillable = [
        'code',
        'development_name',
        'total_cost',
        'cost_2025',
        'cost_2026',
        'start_date',
        'end_date',
        'organizations',
        'development_type',
        'current_stage',
    ];

    protected $casts = [
        'total_cost' => 'decimal:2',
        'cost_2025' => 'decimal:2',
        'cost_2026' => 'decimal:2',
    ];
}
