<?php

namespace App\Http\Controllers;

use App\Models\Cost;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CostController extends Controller
{
    /**
     * Отобразить список записей стоимости
     */
    public function index(Request $request)
    {
        $request->user()->can('users.manage') || abort(403);

        $costs = Cost::query()
            ->orderBy('year', 'desc')
            ->paginate(15);

        return Inertia::render('Cost', [
            'costs' => $costs,
        ]);
    }

    /**
     * Создать новую запись стоимости
     */
    public function store(Request $request)
    {
        $request->user()->can('users.manage') || abort(403);

        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'average_monthly_salary' => 'nullable|numeric|min:0',
            'document_volume_coefficient' => 'nullable|numeric|min:0',
            'mandatory_payments_qn' => 'nullable|numeric|min:0',
            'overhead_costs_qnr' => 'nullable|numeric|min:0',
            'profit_qp' => 'nullable|numeric|min:0',
            'other_expenses_qpr' => 'nullable|numeric|min:0',
            'review_cost_sp' => 'nullable|numeric|min:0',
        ]);

        Cost::create([
            'year' => $request->year,
            'average_monthly_salary' => ($request->average_monthly_salary !== null && $request->average_monthly_salary !== '') ? $request->average_monthly_salary : null,
            'document_volume_coefficient' => ($request->document_volume_coefficient !== null && $request->document_volume_coefficient !== '') ? $request->document_volume_coefficient : null,
            'mandatory_payments_qn' => ($request->mandatory_payments_qn !== null && $request->mandatory_payments_qn !== '') ? $request->mandatory_payments_qn : null,
            'overhead_costs_qnr' => ($request->overhead_costs_qnr !== null && $request->overhead_costs_qnr !== '') ? $request->overhead_costs_qnr : null,
            'profit_qp' => ($request->profit_qp !== null && $request->profit_qp !== '') ? $request->profit_qp : null,
            'other_expenses_qpr' => ($request->other_expenses_qpr !== null && $request->other_expenses_qpr !== '') ? $request->other_expenses_qpr : null,
            'review_cost_sp' => ($request->review_cost_sp !== null && $request->review_cost_sp !== '') ? $request->review_cost_sp : null,
        ]);

        return redirect()->back()->with('success', 'Запись успешно добавлена');
    }

    /**
     * Обновить существующую запись стоимости
     */
    public function update(Request $request, Cost $cost)
    {
        $request->user()->can('users.manage') || abort(403);

        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'average_monthly_salary' => 'nullable|numeric|min:0',
            'document_volume_coefficient' => 'nullable|numeric|min:0',
            'mandatory_payments_qn' => 'nullable|numeric|min:0',
            'overhead_costs_qnr' => 'nullable|numeric|min:0',
            'profit_qp' => 'nullable|numeric|min:0',
            'other_expenses_qpr' => 'nullable|numeric|min:0',
            'review_cost_sp' => 'nullable|numeric|min:0',
        ]);

        $cost->update([
            'year' => $request->year,
            'average_monthly_salary' => ($request->average_monthly_salary !== null && $request->average_monthly_salary !== '') ? $request->average_monthly_salary : null,
            'document_volume_coefficient' => ($request->document_volume_coefficient !== null && $request->document_volume_coefficient !== '') ? $request->document_volume_coefficient : null,
            'mandatory_payments_qn' => ($request->mandatory_payments_qn !== null && $request->mandatory_payments_qn !== '') ? $request->mandatory_payments_qn : null,
            'overhead_costs_qnr' => ($request->overhead_costs_qnr !== null && $request->overhead_costs_qnr !== '') ? $request->overhead_costs_qnr : null,
            'profit_qp' => ($request->profit_qp !== null && $request->profit_qp !== '') ? $request->profit_qp : null,
            'other_expenses_qpr' => ($request->other_expenses_qpr !== null && $request->other_expenses_qpr !== '') ? $request->other_expenses_qpr : null,
            'review_cost_sp' => ($request->review_cost_sp !== null && $request->review_cost_sp !== '') ? $request->review_cost_sp : null,
        ]);

        return redirect()->back()->with('success', 'Запись успешно обновлена');
    }

    /**
     * Удалить запись стоимости
     */
    public function destroy(Request $request, Cost $cost)
    {
        $request->user()->can('users.manage') || abort(403);

        $cost->delete();

        return redirect()->back()->with('success', 'Запись успешно удалена');
    }
}

