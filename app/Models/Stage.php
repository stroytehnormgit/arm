<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    protected $table = 'stages';

    protected $fillable = [
        'name',
    ];

    /**
     * Планируемые перечни, связанные с этим этапом
     */
    public function plannedLists()
    {
        return $this->belongsToMany(\App\Models\PlannedList::class, 'planned_list_stage')
            ->withPivot('start_date', 'end_date', 'amount')
            ->withTimestamps();
    }
}

