<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PlannedList extends Model
{
    use LogsActivity;
    protected $table = 'planned_list';
    
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
        'document_type',
        'designation',
        'development_end',
        'page_count',
        'development_start',
        'block',
        'author',
        'cost',
        'department',
        'regulatory_documents',
        'first_year_stages',
        'subsequent_years_stages',
    ];

    protected $casts = [
        'total_cost' => 'decimal:2',
        'cost_2025' => 'decimal:2',
        'cost_2026' => 'decimal:2',
    ];

    /**
     * Этапы, связанные с этой записью планируемого перечня
     */
    public function stages()
    {
        return $this->belongsToMany(Stage::class, 'planned_list_stage')
            ->withPivot('start_date', 'end_date', 'amount')
            ->withTimestamps();
    }

    /**
     * Настройка логирования активности
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['code', 'development_name', 'total_cost', 'cost_2025', 'cost_2026', 'start_date', 'end_date', 'organizations', 'development_type', 'department'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => 'Создана запись планируемого перечня',
                'updated' => 'Обновлена запись планируемого перечня',
                'deleted' => 'Удалена запись планируемого перечня',
                default => "Действие с записью планируемого перечня: {$eventName}",
            });
    }
}
