<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cost extends Model
{
    protected $table = 'costs';
    
    protected $fillable = [
        'year',
        'average_monthly_salary',
        'document_volume_coefficient',
        'mandatory_payments_qn',
        'overhead_costs_qnr',
        'profit_qp',
        'other_expenses_qpr',
        'review_cost_sp',
    ];

    protected $casts = [
        'year' => 'integer',
        'average_monthly_salary' => 'decimal:4',
        'document_volume_coefficient' => 'decimal:4',
        'mandatory_payments_qn' => 'decimal:4',
        'overhead_costs_qnr' => 'decimal:4',
        'profit_qp' => 'decimal:4',
        'other_expenses_qpr' => 'decimal:4',
        'review_cost_sp' => 'decimal:4',
    ];
}

