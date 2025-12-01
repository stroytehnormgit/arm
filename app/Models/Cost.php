<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Cost extends Model
{
    use LogsActivity;
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

    /**
     * Настройка логирования активности
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['year', 'average_monthly_salary', 'document_volume_coefficient', 'mandatory_payments_qn', 'overhead_costs_qnr', 'profit_qp', 'other_expenses_qpr', 'review_cost_sp'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => 'Созданы данные стоимости',
                'updated' => 'Обновлены данные стоимости',
                'deleted' => 'Удалены данные стоимости',
                default => "Действие с данными стоимости: {$eventName}",
            });
    }
}

