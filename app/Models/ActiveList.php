<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ActiveList extends Model
{
    use LogsActivity;
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

    /**
     * Настройка логирования активности
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['code', 'development_name', 'total_cost', 'cost_2025', 'cost_2026', 'start_date', 'end_date', 'organizations', 'development_type', 'current_stage'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => 'Создана запись действующего перечня',
                'updated' => 'Обновлена запись действующего перечня',
                'deleted' => 'Удалена запись действующего перечня',
                default => "Действие с записью действующего перечня: {$eventName}",
            });
    }
}
