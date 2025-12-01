<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;

class ActivityLogController extends Controller
{
    /**
     * Отобразить список логов активности
     */
    public function index(Request $request)
    {
        $request->user()->can('users.manage') || abort(403);

        $query = Activity::with(['causer', 'subject'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id)
                  ->where('causer_type', User::class);
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('description', 'like', '%' . $search . '%');
        }

        $activities = $query->paginate(20);

        $users = User::orderBy('name')->get(['id', 'name', 'email']);

        $subjectTypes = Activity::select('subject_type')
            ->whereNotNull('subject_type')
            ->distinct()
            ->orderBy('subject_type')
            ->pluck('subject_type')
            ->map(function ($type) {
                return [
                    'value' => $type,
                    'label' => class_basename($type),
                ];
            })
            ->values();

        $events = Activity::select('event')
            ->whereNotNull('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event')
            ->map(function ($event) {
                return [
                    'value' => $event,
                    'label' => match($event) {
                        'created' => 'Создание',
                        'updated' => 'Обновление',
                        'deleted' => 'Удаление',
                        default => ucfirst($event),
                    },
                ];
            })
            ->values();

        return Inertia::render('ActivityLog', [
            'activities' => $activities,
            'users' => $users,
            'subjectTypes' => $subjectTypes,
            'events' => $events,
            'filters' => $request->only(['user_id', 'subject_type', 'event', 'search']),
        ]);
    }
}

