<?php

namespace App\Http\Controllers;

use App\Models\ActiveList;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActiveListController extends Controller
{
    /**
     * Отобразить список активных записей с фильтрацией
     */
    public function index(Request $request)
    {
        $query = ActiveList::query();

        if ($request->filled('name')) {
            $query->where('development_name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('development_type')) {
            $query->where('development_type', $request->development_type);
        }

        if ($request->filled('stage')) {
            $query->where('current_stage', $request->stage);
        }

        if ($request->filled('department')) {
            $query->where('organizations', 'like', '%' . $request->department . '%');
        }

        $activeList = $query->orderBy('code')->paginate(15);

        $developmentTypes = ActiveList::select('development_type')
            ->whereNotNull('development_type')
            ->where('development_type', '!=', '')
            ->distinct()
            ->orderBy('development_type')
            ->pluck('development_type')
            ->toArray();

        $stages = ActiveList::select('current_stage')
            ->whereNotNull('current_stage')
            ->where('current_stage', '!=', '')
            ->distinct()
            ->orderBy('current_stage')
            ->pluck('current_stage')
            ->toArray();

        $departments = \App\Models\User::select('department')
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department')
            ->toArray();

        return Inertia::render('ActiveList', [
            'activeList' => $activeList,
            'filters' => $request->only(['name', 'development_type', 'stage', 'department']),
            'developmentTypes' => $developmentTypes,
            'stages' => $stages,
            'departments' => $departments,
        ]);
    }

    /**
     * Создать новую запись в активном перечне
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:255',
            'development_name' => 'required|string|max:255',
            'total_cost' => 'required|numeric|min:0',
            'cost_2025' => 'required|numeric|min:0',
            'cost_2026' => 'required|numeric|min:0',
            'start_date' => 'required|string|max:255',
            'end_date' => 'required|string|max:255',
            'organizations' => 'required|string|max:255',
            'development_type' => 'required|string|max:255',
            'current_stage' => 'required|string|max:255',
        ]);

        ActiveList::create($request->all());

        return redirect()->back()->with('success', 'Запись успешно добавлена');
    }

    /**
     * Обновить существующую запись в активном перечне
     */
    public function update(Request $request, ActiveList $activeList)
    {
        $request->validate([
            'code' => 'required|string|max:255',
            'development_name' => 'required|string|max:255',
            'total_cost' => 'required|numeric|min:0',
            'cost_2025' => 'required|numeric|min:0',
            'cost_2026' => 'required|numeric|min:0',
            'start_date' => 'required|string|max:255',
            'end_date' => 'required|string|max:255',
            'organizations' => 'required|string|max:255',
            'development_type' => 'required|string|max:255',
            'current_stage' => 'required|string|max:255',
        ]);

        $activeList->update($request->all());

        return redirect()->back()->with('success', 'Запись успешно обновлена');
    }

    /**
     * Удалить запись из активного перечня
     */
    public function destroy(ActiveList $activeList)
    {
        $activeList->delete();

        return redirect()->back()->with('success', 'Запись успешно удалена');
    }
}
