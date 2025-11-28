<?php

namespace App\Http\Controllers;

use App\Models\Stage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StagesController extends Controller
{
    /**
     * Отобразить список этапов с фильтрацией
     */
    public function index(Request $request)
    {
        $request->user()->can('stages.manage') || abort(403);

        $query = Stage::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', '%' . $search . '%');
        }

        $stages = $query->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Stages', [
            'stages' => $stages,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Создать новый этап
     */
    public function store(Request $request)
    {
        $request->user()->can('stages.manage') || abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Stage::create([
            'name' => $request->name,
        ]);

        return redirect()->back()->with('success', 'Этап успешно создан');
    }

    /**
     * Обновить существующий этап
     */
    public function update(Request $request, Stage $stage)
    {
        $request->user()->can('stages.manage') || abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $stage->name = $request->name;
        $stage->save();

        return redirect()->back()->with('success', 'Этап успешно обновлен');
    }

    /**
     * Удалить этап
     */
    public function destroy(Request $request, Stage $stage)
    {
        $request->user()->can('stages.manage') || abort(403);

        $stage->delete();

        return redirect()->back()->with('success', 'Этап успешно удален');
    }
}

