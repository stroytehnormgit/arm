<?php

namespace App\Http\Controllers;

use App\Models\Archive;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ArchiveController extends Controller
{
    /**
     * Отобразить список архивных записей с фильтрацией
     */
    public function index(Request $request)
    {
        $activeYear = $request->get('year', '2023-2024');
        
        $query = Archive::query();

        $query->where('year_period', $activeYear);

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('organization')) {
            $query->where('organization', 'like', '%' . $request->organization . '%');
        }

        $archives = $query->orderBy('code')->paginate(15);

        return Inertia::render('Archive', [
            'activeYear' => $activeYear,
            'archives' => $archives,
            'filters' => $request->only(['name', 'type', 'organization'])
        ]);
    }

    /**
     * Создать новую архивную запись
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'total_cost' => 'required|numeric|min:0',
            'cost_2023' => 'required|numeric|min:0',
            'cost_2024' => 'required|numeric|min:0',
            'start_date' => 'required|string|max:255',
            'end_date' => 'required|string|max:255',
            'organization' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'year_period' => 'required|string|max:255',
        ]);

        Archive::create($request->all());

        return redirect()->back()->with('success', 'Архивная запись успешно добавлена');
    }

    /**
     * Обновить существующую архивную запись
     */
    public function update(Request $request, Archive $archive)
    {
        $request->validate([
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'total_cost' => 'required|numeric|min:0',
            'cost_2023' => 'required|numeric|min:0',
            'cost_2024' => 'required|numeric|min:0',
            'start_date' => 'required|string|max:255',
            'end_date' => 'required|string|max:255',
            'organization' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'year_period' => 'required|string|max:255',
        ]);

        $archive->update($request->all());

        return redirect()->back()->with('success', 'Архивная запись успешно обновлена');
    }

    /**
     * Удалить архивную запись
     */
    public function destroy(Archive $archive)
    {
        $archive->delete();
        return redirect()->back()->with('success', 'Архивная запись успешно удалена');
    }
}