<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarPlan extends Model
{
    protected $table = 'calendar_plans';
    
    protected $fillable = [
        'number',
        'stage',
        'deadline',
        'amount',
        'result',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];
}