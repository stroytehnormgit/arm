<?php

namespace App\Http\Controllers;

use App\Models\PlannedList;
use App\Models\ActiveList;
use App\Models\Stage;
use App\Models\Cost;
use App\Services\DepartmentAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
use Inertia\Inertia;

class PlannedListController extends Controller
{
    protected DepartmentAccessService $departmentAccessService;

    public function __construct(DepartmentAccessService $departmentAccessService)
    {
        $this->departmentAccessService = $departmentAccessService;
    }

    /**
     * Отобразить список записей планируемого перечня с фильтрацией
     */
    public function index(Request $request)
    {
        $request->user()->can('planned-list.view') || abort(403);

        $query = PlannedList::query();

        $query = $this->departmentAccessService->filterByDepartmentAccess($query, $request->user());

        if ($request->filled('name')) {
            $query->where('development_name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('development_type')) {
            $query->where('development_type', $request->development_type);
        }

        if ($request->filled('organization')) {
            $query->where('organizations', 'like', '%' . $request->organization . '%');
        }

        $plannedList = $query->with('stages')->orderBy('code')->paginate(15);

        $stages = Stage::orderBy('name')->get();

        $currentYear = now()->year;
        $costData = Cost::where('year', $currentYear)->first();

        return Inertia::render('PlannedList', [
            'plannedList' => $plannedList,
            'stages' => $stages,
            'filters' => $request->only(['name', 'development_type', 'organization']),
            'costData' => $costData ? [
                'average_monthly_salary' => $costData->average_monthly_salary,
                'document_volume_coefficient' => $costData->document_volume_coefficient,
                'mandatory_payments_qn' => $costData->mandatory_payments_qn,
                'overhead_costs_qnr' => $costData->overhead_costs_qnr,
                'profit_qp' => $costData->profit_qp,
                'other_expenses_qpr' => $costData->other_expenses_qpr,
                'review_cost_sp' => $costData->review_cost_sp,
            ] : null,
        ]);
    }

    /**
     * Создать новую запись в планируемом перечне
     */
    public function store(Request $request)
    {
        $request->user()->can('planned-list.create') || abort(403);

        if (!$request->user()->hasRole('admin')) {
            $request->merge(['department' => $request->user()->department]);
        }

        $request->validate([
            'document_type' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'development_end' => 'nullable|date',
            'development_type' => 'nullable|string|max:255',
            'page_count' => 'nullable|integer|min:0',
            'development_start' => 'nullable|date',
            'block' => 'nullable|string|max:255',
            'author' => 'nullable|string|max:255',
            'cost' => 'nullable|numeric|min:0',
            'department' => 'nullable|string|max:255',
            'development_name' => 'required|string|max:255',
            'organizations' => 'nullable|string|max:500',
            'regulatory_documents' => 'nullable|string|max:1000',
            'first_year_stages' => 'nullable|string|max:1000',
            'subsequent_years_stages' => 'nullable|string|max:1000',
        ]);

        $code = $request->filled('block')
            ? $this->generateNextCodeForBlock((string) $request->block)
            : 'PL-' . str_pad(PlannedList::count() + 1, 4, '0', STR_PAD_LEFT);
        
        $data = $request->all();
        $data['code'] = $code;
        $data['designation'] = $code;
        
        if ($request->user()->hasRole('admin')) {
            $data['total_cost'] = $request->filled('cost') && $request->cost !== '' ? $request->cost : null;
            $data['cost_2025'] = $request->filled('cost_2025') && $request->cost_2025 !== '' ? $request->cost_2025 : null;
            $data['cost_2026'] = null;
        } else {
            $data['total_cost'] = null;
            $data['cost_2025'] = null;
            $data['cost_2026'] = null;
            unset($data['cost'], $data['cost_2025']);
        }
        $data['start_date'] = $request->development_start ?? now()->format('Y-m-d');
        $data['end_date'] = $request->development_end ?? now()->addYear()->format('Y-m-d');

        $plannedList = PlannedList::create($data);

        if ($request->filled('stages')) {
            $stagesData = [];
            foreach ($request->stages as $stageData) {
                if (isset($stageData['stage_id']) && $stageData['stage_id']) {
                    $stagesData[$stageData['stage_id']] = [
                        'start_date' => $stageData['start_date'] ?? null,
                        'end_date' => $stageData['end_date'] ?? null,
                        'amount' => $stageData['amount'] ?? null,
                    ];
                }
            }
            $plannedList->stages()->sync($stagesData);
        }

        return redirect()->back()->with('success', 'Предложение успешно добавлено');
    }

    /**
     * Обновить существующую запись в планируемом перечне
     */
    public function update(Request $request, PlannedList $plannedList)
    {
        $request->user()->can('planned-list.edit') || abort(403);

        if (!$this->departmentAccessService->userHasAccessToDepartment($request->user(), $plannedList->department ?? '')) {
            abort(403, 'У вас нет доступа к редактированию этой записи');
        }

        $request->validate([
            'document_type' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'development_end' => 'nullable|date',
            'development_type' => 'nullable|string|max:255',
            'page_count' => 'nullable|integer|min:0',
            'development_start' => 'nullable|date',
            'block' => 'nullable|string|max:255',
            'author' => 'nullable|string|max:255',
            'cost' => 'nullable|numeric|min:0',
            'department' => 'nullable|string|max:255',
            'development_name' => 'required|string|max:255',
            'organizations' => 'nullable|string|max:500',
            'regulatory_documents' => 'nullable|string|max:1000',
            'first_year_stages' => 'nullable|string|max:1000',
            'subsequent_years_stages' => 'nullable|string|max:1000',
        ]);

        $data = $request->all();
        if ($request->filled('block') && $request->block !== $plannedList->block) {
            $newCode = $this->generateNextCodeForBlock((string) $request->block);
            $data['code'] = $newCode;
            $data['designation'] = $newCode;
        } else {
            $data['code'] = $plannedList->code;
            $data['designation'] = $plannedList->designation ?: $plannedList->code;
        }
        
        if ($request->user()->hasRole('admin')) {
            $data['total_cost'] = $request->filled('cost') && $request->cost !== '' ? $request->cost : $plannedList->total_cost;
            $data['cost_2025'] = $request->filled('cost_2025') && $request->cost_2025 !== '' ? $request->cost_2025 : $plannedList->cost_2025;
        } else {
            $data['total_cost'] = $plannedList->total_cost;
            $data['cost_2025'] = $plannedList->cost_2025;
            unset($data['cost'], $data['cost_2025']);
        }
        $data['start_date'] = $request->development_start ?? $plannedList->start_date;
        $data['end_date'] = $request->development_end ?? $plannedList->end_date;

        $plannedList->update($data);

        if ($request->filled('stages')) {
            $stagesData = [];
            foreach ($request->stages as $stageData) {
                if (isset($stageData['stage_id']) && $stageData['stage_id']) {
                    $stagesData[$stageData['stage_id']] = [
                        'start_date' => $stageData['start_date'] ?? null,
                        'end_date' => $stageData['end_date'] ?? null,
                        'amount' => $stageData['amount'] ?? null,
                    ];
                }
            }
            $plannedList->stages()->sync($stagesData);
        } else {
            $plannedList->stages()->sync([]);
        }

        return redirect()->back()->with('success', 'Запись успешно обновлена');
    }

    /**
     * Удалить запись из планируемого перечня
     */
    public function destroy(Request $request, PlannedList $plannedList)
    {
        $request->user()->can('planned-list.delete') || abort(403);

        if (!$this->departmentAccessService->userHasAccessToDepartment($request->user(), $plannedList->department ?? '')) {
            abort(403, 'У вас нет доступа к удалению этой записи');
        }

        $plannedList->delete();

        return redirect()->back()->with('success', 'Запись успешно удалена');
    }

    /**
     * Перенести все записи из планируемого перечня в действующий
     * Устанавливает current_stage = "ТЗ" по умолчанию
     */
    public function approve(Request $request)
    {
        if (!$request->user()->hasRole('admin')) {
            abort(403, 'Только администраторы могут утверждать перечень');
        }

        $plannedItems = PlannedList::all();

        if ($plannedItems->isEmpty()) {
            return redirect()->back()->with('error', 'Нет записей для утверждения');
        }

        DB::beginTransaction();
        try {
            foreach ($plannedItems as $plannedItem) {
                $exists = ActiveList::where('code', $plannedItem->code)->exists();
                if ($exists) {
                    continue;
                }

                $startDate = $plannedItem->development_start 
                    ? date('d.m.Y', strtotime($plannedItem->development_start))
                    : ($plannedItem->start_date ?? '');
                
                $endDate = $plannedItem->development_end 
                    ? date('d.m.Y', strtotime($plannedItem->development_end))
                    : ($plannedItem->end_date ?? '');

                if (empty($plannedItem->code) || empty($plannedItem->development_name)) {
                    continue;
                }

                ActiveList::create([
                    'code' => $plannedItem->code,
                    'development_name' => $plannedItem->development_name,
                    'total_cost' => $plannedItem->total_cost ?? 0,
                    'cost_2025' => $plannedItem->cost_2025 ?? 0,
                    'cost_2026' => $plannedItem->cost_2026 ?? 0,
                    'start_date' => $startDate ?: '',
                    'end_date' => $endDate ?: '',
                    'organizations' => $plannedItem->organizations ?? '',
                    'development_type' => $plannedItem->development_type ?? '',
                    'current_stage' => 'ТЗ',
                ]);
            }

            DB::commit();
            
            activity()
                ->causedBy($request->user())
                ->withProperties([
                    'planned_items_count' => $plannedItems->count(),
                    'active_items_created' => ActiveList::whereIn('code', $plannedItems->pluck('code'))->count(),
                ])
                ->log('Утвержден планируемый перечень');
            
            return redirect()->back()->with('success', 'Перечень успешно утвержден. Все записи перенесены в действующий перечень с этапом "ТЗ"');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Ошибка при утверждении перечня: ' . $e->getMessage());
        }
    }

    /**
     * Экспортировать выбранные записи в Word документ
     */
    public function export(Request $request)
    {
        $request->user()->can('planned-list.export') || abort(403);

        if (!extension_loaded('zip') || !class_exists('ZipArchive')) {
            return redirect()->back()->with('error', 'Для экспорта в Word необходимо установить PHP расширение zip. Перезапустите веб-сервер после включения extension=zip в php.ini');
        }

        $ids = explode(',', $request->get('ids', ''));
        $ids = array_filter($ids, fn($id) => is_numeric($id));
        
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Не выбраны записи для экспорта');
        }

        $query = PlannedList::whereIn('id', $ids);
        
        $query = $this->departmentAccessService->filterByDepartmentAccess($query, $request->user());
        
        $items = $query
            ->orderBy('development_name')
            ->orderBy('code')
            ->get();

        if ($items->isEmpty()) {
            return redirect()->back()->with('error', 'Записи не найдены');
        }

        activity()
            ->causedBy($request->user())
            ->withProperties([
                'exported_items_count' => $items->count(),
                'exported_ids' => $ids,
            ])
            ->log('Экспортирован планируемый перечень в Word');

        $grouped = $items->groupBy('development_name');

        $formatDate = function($dateStr) {
            if (!$dateStr) return '';
            
            $months = [
                'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
                'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
            ];
            
            try {
                $date = \Carbon\Carbon::parse($dateStr);
                return $months[$date->month - 1] . ' ' . $date->year;
            } catch (\Exception $e) {
                return $dateStr;
            }
        };

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        
        $section = $phpWord->addSection([
            'orientation' => 'landscape',
            'marginLeft' => 600,  
            'marginRight' => 600,
            'marginTop' => 600,
            'marginBottom' => 600,
            'pageSizeW' => 16838, 
            'pageSizeH' => 11906, 
        ]);

        $section->addText(
            'Приложение',
            ['size' => 12, 'name' => 'Times New Roman'],
            ['alignment' => 'right', 'spaceAfter' => 0]
        );
        
        $section->addText(
            'УТВЕРЖДЕНО',
            ['size' => 12, 'name' => 'Times New Roman', 'bold' => true],
            ['alignment' => 'right', 'spaceAfter' => 0]
        );
        
        $section->addText(
            'Постановление коллегии',
            ['size' => 12, 'name' => 'Times New Roman'],
            ['alignment' => 'right', 'spaceAfter' => 0]
        );
        
        $section->addText(
            'Минстройархитектуры',
            ['size' => 12, 'name' => 'Times New Roman'],
            ['alignment' => 'right', 'spaceAfter' => 0]
        );
        
        $section->addText(
            'от _______№ ____________',
            ['size' => 12, 'name' => 'Times New Roman'],
            ['alignment' => 'right', 'spaceAfter' => 240]
        );

        $totalCost = $items->sum('total_cost') ?? 0;
        $cost2025 = $items->sum('cost_2025') ?? 0;
        $cost2026 = $items->sum('cost_2026') ?? 0;
        
        $section->addText(
            'Всего стоимость работ: ' . number_format($totalCost, 2, ',', ' ') . ' руб.',
            ['size' => 12, 'name' => 'Times New Roman'],
            ['alignment' => 'left', 'spaceAfter' => 0]
        );
        
        $section->addText(
            'в том числе: на 2025 год: ' . number_format($cost2025, 2, ',', ' ') . ' руб.',
            ['size' => 12, 'name' => 'Times New Roman'],
            ['alignment' => 'left', 'spaceAfter' => 0]
        );
        
        $section->addText(
            'на 2026 год: ' . number_format($cost2026, 2, ',', ' ') . ' руб.',
            ['size' => 12, 'name' => 'Times New Roman'],
            ['alignment' => 'left', 'spaceAfter' => 240]
        );

        $section->addText(
            'Перечень работ по разработке ТНПА и внесению изменений в ТНПА в области архитектурной, градостроительной и строительной деятельности в 2025-2026 гг.',
            ['bold' => true, 'size' => 12, 'name' => 'Times New Roman'],
            ['alignment' => 'center', 'spaceAfter' => 120]
        );
        
        $textRun = $section->addTextRun(['alignment' => 'center', 'spaceAfter' => 240]);
        $textRun->addText(
            '(вновь начинаемая тематика)',
            ['size' => 12, 'name' => 'Times New Roman', 'italic' => true]
        );

        $blocks = [
            '1.01' => '1.01 Техническое нормирование, стандартизация, сертификация и метрология',
            '1.02' => '1.02 Предпроектные и проектные работы',
            '1.03' => '1.03 Организация строительного производства',
            '1.04' => '1.04 Эксплуатация',
            '2.01' => '2.01 Основные положения надежности зданий и сооружений',
            '2.02' => '2.02 Пожарная безопасность',
            '2.03' => '2.03 Защита от опасных геофизических и техногенных воздействий',
            '2.04' => '2.04 Внутренний климат и защита от вредных воздействий',
            '2.05' => '2.05 Размерная взаимозаменяемость и совместимость',
            '3.01' => '3.01 Градостроительство',
            '3.02' => '3.02 Жилые, общественные и производственные здания и сооружения, благоустройство территорий',
            '3.03' => '3.03 Сооружения транспорта и транспортная инфраструктура',
            '3.04' => '3.04 Гидротехнические и мелиоративные сооружения',
            '3.05' => '3.05 Магистральные и промысловые трубопроводы',
            '4.01' => '4.01 Водоснабжение и водоотведение',
            '4.02' => '4.02 Теплоснабжение и холодоснабжение, отопление, вентиляция и кондиционирование воздуха',
            '4.03' => '4.03 Газоснабжение',
            '4.04' => '4.04 Электроснабжение, электросиловое оборудование и электрическое освещение, телефонизация, радиофикация и телефикация',
            '5.01' => '5.01 Основания и фундаменты зданий и сооружений',
            '5.02' => '5.02 Каменные и армокаменные конструкции',
            '5.03' => '5.03 Железобетонные и бетонные конструкции и изделия',
            '5.04' => '5.04 Металлические конструкции и изделия',
            '5.05' => '5.05 Деревянные конструкции и изделия',
            '5.06' => '5.06 Конструкции и изделия из других материалов',
            '5.07' => '5.07 Светопрозрачные ограждения в различных конструктивных исполнениях, двери, ворота и приборы к ним',
            '5.08' => '5.08 Кровли, изоляционные покрытия',
            '5.09' => '5.09 Полы, отделочные и защитные покрытия',
            '6.01' => '6.01 Стеновые кладочные изделия',
            '6.02' => '6.02 Минеральные вяжущие материалы',
            '6.03' => '6.03 Бетоны и растворы',
            '6.04' => '6.04 Щебень, гравий и песок для строительных работ',
            '6.05' => '6.05 Теплоизоляционные, звукоизоляционные и звукопоглощающие материалы и изделия',
            '6.06' => '6.06 Кровельные, гидроизоляционные и герметизирующие материалы и изделия',
            '6.07' => '6.07 Отделочные и облицовочные материалы и изделия',
            '6.08' => '6.08 Асбестоцементные изделия',
            '6.09' => '6.09 Дорожные материалы',
            '6.10' => '6.10 Строительное стекло',
            '6.11' => '6.11 Композитные и полимерные материалы и изделия',
            '7.01' => '7.01 Мобильные здания и сооружения',
            '7.02' => '7.02 Специализированная оснастка предприятий стройиндустрии',
            '7.03' => '7.03 Оснастка строительных организаций',
        ];
        
        $getBlockName = function($blockCode) use ($blocks) {
            return $blocks[$blockCode] ?? ($blockCode ? "Блок $blockCode" : '');
        };

        $columnWidths = [
            1441,
            2953,
            1351,
            1441,
            1441,
            1182,
            1182,
            2557,
            2306,
        ];

        $table = $section->addTable([
            'unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP, // TWIP = 'dxa' (twentieths of a point)
            'width' => 15854, // Ширина таблицы из анализа
            'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMarginTop' => 100,    // Минимальные отступы: ~0.18 см (100 twips)
            'cellMarginLeft' => 100,
            'cellMarginRight' => 100,
            'cellMarginBottom' => 100,
        ]);

        $headers = [
            '№ п/п (шифр)',
            'Наименование разработки',
            'Стоимость всего',
            'Стоимость на 2025',
            'Стоимость на 2026',
            'Срок начала разработки (месяц, год)',
            'Срок окончания разработки (месяц, год)',
            'Наименование организаций, выполняющих работу, и номер Технического Комитета',
            'Разрабатывается впервые или взамен действующих нормативных документов',
        ];

        $addTableHeaders = function($table, $headers, $columnWidths) {
            $table->addRow(400, ['tblHeader' => true]); // tblHeader = повтор на новой странице
            foreach ($headers as $index => $header) {
                $table->addCell($columnWidths[$index], [
                    'valign' => 'top',
                ])->addText($header, ['bold' => true, 'size' => 10, 'name' => 'Times New Roman'], [
                    'alignment' => 'center',
                    'spaceAfter' => 0,
                ]);
            }
        };
        
        $rowHeight = 300;
        $blockHeaderHeight = 300;

        $addTableHeaders($table, $headers, $columnWidths);

        $previousBlock = null;
        $isFirstBlock = true;
        foreach ($grouped as $name => $groupItems) {
            $currentBlock = $groupItems->first()->block ?? null;
            
            if ($currentBlock && $currentBlock !== $previousBlock) {
                $blockName = $getBlockName($currentBlock);
                if ($blockName) {
                    $table->addRow($blockHeaderHeight);
                    $headerCell = $table->addCell(array_sum($columnWidths), [
                        'gridSpan' => count($columnWidths),
                        'valign' => 'center',
                    ]);
                    $headerCell->addText(
                        'Блок ' . $blockName,
                        ['bold' => true, 'size' => 10, 'name' => 'Times New Roman'],
                        ['alignment' => 'center', 'spaceAfter' => 0]
                    );
                }
                $previousBlock = $currentBlock;
                $isFirstBlock = false;
            }
            
            foreach ($groupItems as $item) {
                $table->addRow($rowHeight);
                
                $cellStyle = ['valign' => 'top'];
                
                $table->addCell($columnWidths[0], $cellStyle)->addText(
                    $item->code ?? '',
                    ['size' => 10, 'name' => 'Times New Roman'],
                    ['alignment' => 'center']
                );
                
                $table->addCell($columnWidths[1], $cellStyle)->addText(
                    $item->development_name ?? '',
                    ['size' => 10, 'name' => 'Times New Roman'],
                    ['alignment' => 'center']
                );
                
                $costCell = $table->addCell($columnWidths[2], $cellStyle);
                if ($item->total_cost) {
                    $costCell->addText(
                        number_format($item->total_cost, 0, ',', ' '),
                        ['size' => 10, 'name' => 'Times New Roman'],
                        ['alignment' => 'center']
                    );
                }
                if ($item->page_count) {
                    $costCell->addText(
                        $item->page_count . ' стр.',
                        ['size' => 10, 'name' => 'Times New Roman'],
                        ['alignment' => 'center']
                    );
                }
                
                $table->addCell($columnWidths[3], $cellStyle)->addText(
                    $item->cost_2025 ? number_format($item->cost_2025, 0, ',', ' ') : '',
                    ['size' => 10, 'name' => 'Times New Roman'],
                    ['alignment' => 'center']
                );
                
                $table->addCell($columnWidths[4], $cellStyle)->addText(
                    $item->cost_2026 ? number_format($item->cost_2026, 0, ',', ' ') : '',
                    ['size' => 10, 'name' => 'Times New Roman'],
                    ['alignment' => 'center']
                );
                
                $table->addCell($columnWidths[5], $cellStyle)->addText(
                    $formatDate($item->development_start ?? $item->start_date),
                    ['size' => 10, 'name' => 'Times New Roman'],
                    ['alignment' => 'center']
                );
                
                $table->addCell($columnWidths[6], $cellStyle)->addText(
                    $formatDate($item->development_end ?? $item->end_date),
                    ['size' => 10, 'name' => 'Times New Roman'],
                    ['alignment' => 'center']
                );
                
                $table->addCell($columnWidths[7], $cellStyle)->addText(
                    $item->organizations ?? '',
                    ['size' => 10, 'name' => 'Times New Roman'],
                    ['alignment' => 'center']
                );
                
                $table->addCell($columnWidths[8], $cellStyle)->addText(
                    $item->development_type ?? '',
                    ['size' => 10, 'name' => 'Times New Roman'],
                    ['alignment' => 'center']
                );
            }
        }

        $filename = 'Перечень_' . date('Y-m-d_H-i-s') . '.docx';
        
        $docsPath = public_path('docs');
        
        if (!File::exists($docsPath)) {
            File::makeDirectory($docsPath, 0755, true);
        }
        
        $filePath = $docsPath . '/' . $filename;
        
        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($filePath);

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(false);
    }

    /**
     * Предпросмотр выбранных записей для экспорта
     */
    public function preview(Request $request)
    {
        $request->user()->can('planned-list.export') || abort(403);

        $ids = explode(',', $request->get('ids', ''));
        $ids = array_filter($ids, fn($id) => is_numeric($id));
        
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Не выбраны записи для предпросмотра');
        }

        $query = PlannedList::whereIn('id', $ids);
        
        $query = $this->departmentAccessService->filterByDepartmentAccess($query, $request->user());
        
        $items = $query
            ->orderBy('development_name')
            ->orderBy('code')
            ->get();

        if ($items->isEmpty()) {
            return redirect()->back()->with('error', 'Записи не найдены');
        }

        $grouped = $items->groupBy('development_name');

        $formatDate = function($dateStr) {
            if (!$dateStr) return '';
            
            $months = [
                'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
                'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
            ];
            
            try {
                $date = \Carbon\Carbon::parse($dateStr);
                return $months[$date->month - 1] . ' ' . $date->year;
            } catch (\Exception $e) {
                return $dateStr;
            }
        };

        return response()->view('preview', [
            'grouped' => $grouped,
            'formatDate' => $formatDate,
        ]);
    }

    /**
     * Получить следующий код для указанного блока
     */
    public function nextCode(Request $request)
    {
        $request->user()->can('planned-list.create') || abort(403);

        $block = trim((string) $request->get('block', ''));
        if ($block === '') {
            return response()->json(['error' => 'block is required'], 422);
        }

        $codes = PlannedList::query()
            ->where('block', $block)
            ->pluck('code')
            ->filter();

        $maxSeq = 0;
        foreach ($codes as $code) {
            $pattern = '/^2\.' . preg_quote($block, '/') . '\.(\d+)$/u';
            if (preg_match($pattern, (string) $code, $m)) {
                $num = (int) $m[1];
                if ($num > $maxSeq) {
                    $maxSeq = $num;
                }
            }
        }

        $nextSeq = $maxSeq + 1;
        $nextCode = '2.' . $block . '.' . $nextSeq;

        return response()->json([
            'next' => $nextSeq,
            'code' => $nextCode,
        ]);
    }

    /**
     * Сгенерировать следующий код для указанного блока
     */
    private function generateNextCodeForBlock(string $block): string
    {
        $codes = PlannedList::query()
            ->where('block', $block)
            ->pluck('code')
            ->filter();

        $maxSeq = 0;
        foreach ($codes as $code) {
            $pattern = '/^2\.' . preg_quote($block, '/') . '\.(\d+)$/u';
            if (preg_match($pattern, (string) $code, $m)) {
                $num = (int) $m[1];
                if ($num > $maxSeq) {
                    $maxSeq = $num;
                }
            }
        }

        $nextSeq = $maxSeq + 1;
        return '2.' . $block . '.' . $nextSeq;
    }
}
