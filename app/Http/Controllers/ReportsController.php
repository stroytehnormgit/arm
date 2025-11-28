<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\CalendarPlan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportsController extends Controller
{
    /**
     * Отобразить список отчетов и календарных планов с фильтрацией
     */
    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'stages');
        
        $reportsQuery = Report::query();
        if ($request->filled('name')) {
            $reportsQuery->where('nazvanie', 'like', '%' . $request->name . '%');
        }
        if ($request->filled('etap')) {
            $reportsQuery->where('etap', $request->etap);
        }
        if ($request->filled('razrabotchik')) {
            $reportsQuery->where('razrabotchik', 'like', '%' . $request->razrabotchik . '%');
        }
        if ($request->filled('period')) {
            $reportsQuery->where('period', $request->period);
        }
        $reports = $reportsQuery->orderBy('punkt')->paginate(15);

        $calendarQuery = CalendarPlan::query();
        if ($request->filled('name')) {
            $calendarQuery->where('stage', 'like', '%' . $request->name . '%');
        }
        if ($request->filled('etap')) {
            $calendarQuery->where('result', 'like', '%' . $request->etap . '%');
        }
        if ($request->filled('razrabotchik')) {
            $calendarQuery->where('deadline', 'like', '%' . $request->razrabotchik . '%');
        }
        if ($request->filled('period')) {
            $calendarQuery->where('amount', '>=', $request->period);
        }
        $calendarPlans = $calendarQuery->orderBy('number')->paginate(15);

        return Inertia::render('Reports', [
            'activeTab' => $activeTab,
            'reports' => $reports,
            'calendarPlans' => $calendarPlans,
            'filters' => $request->only(['name', 'etap', 'razrabotchik', 'period'])
        ]);
    }

    /**
     * Создать новый отчет
     */
    public function store(Request $request)
    {
        $request->validate([
            'punkt' => 'required|string|max:255',
            'etap' => 'required|string|max:255',
            'nazvanie' => 'required|string|max:255',
            'razrabotchik' => 'required|string|max:255',
            'stoimost' => 'required|numeric|min:0',
            'period' => 'required|string|max:255',
        ]);

        Report::create($request->all());

        return redirect()->back()->with('success', 'Отчет успешно добавлен');
    }

    /**
     * Обновить существующий отчет
     */
    public function update(Request $request, Report $report)
    {
        $request->validate([
            'punkt' => 'required|string|max:255',
            'etap' => 'required|string|max:255',
            'nazvanie' => 'required|string|max:255',
            'razrabotchik' => 'required|string|max:255',
            'stoimost' => 'required|numeric|min:0',
            'period' => 'required|string|max:255',
        ]);

        $report->update($request->all());

        return redirect()->back()->with('success', 'Отчет успешно обновлен');
    }

    /**
     * Удалить отчет
     */
    public function destroy(Report $report)
    {
        $report->delete();
        return redirect()->back()->with('success', 'Отчет успешно удален');
    }

    /**
     * Создать новый календарный план
     */
    public function storeCalendarPlan(Request $request)
    {
        $request->validate([
            'number' => 'required|string|max:255',
            'stage' => 'required|string|max:255',
            'deadline' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'result' => 'required|string|max:255',
        ]);

        CalendarPlan::create($request->all());

        return redirect()->back()->with('success', 'Календарный план успешно добавлен');
    }

    /**
     * Обновить существующий календарный план
     */
    public function updateCalendarPlan(Request $request, CalendarPlan $calendarPlan)
    {
        $request->validate([
            'number' => 'required|string|max:255',
            'stage' => 'required|string|max:255',
            'deadline' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'result' => 'required|string|max:255',
        ]);

        $calendarPlan->update($request->all());

        return redirect()->back()->with('success', 'Календарный план успешно обновлен');
    }

    /**
     * Удалить календарный план
     */
    public function destroyCalendarPlan(CalendarPlan $calendarPlan)
    {
        $calendarPlan->delete();
        return redirect()->back()->with('success', 'Календарный план успешно удален');
    }
}