<?php

namespace App\Http\Controllers;

use App\Models\UploadedFile;
use App\Models\SiteFile;
use App\Models\MvsFile;
use App\Services\StnbyApiService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FilesController extends Controller
{
    /**
     * Отобразить список файлов с фильтрацией
     */
    public function index(Request $request, StnbyApiService $stnbyService)
    {
        $activeTab = $request->get('tab', 'uploaded');
        
        $uploadedFiles = UploadedFile::query();
        $siteFiles = SiteFile::query();
        $mvsFiles = MvsFile::query();

        if ($request->filled('name')) {
            $uploadedFiles->where('name', 'like', '%' . $request->name . '%');
            $siteFiles->where('development_name', 'like', '%' . $request->name . '%');
            $mvsFiles->where('development_name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('purpose') && $request->purpose !== '') {
            $uploadedFiles->where('purpose', $request->purpose);
        }

        $uploadedFilesLocal = $uploadedFiles->orderBy('date', 'desc')->get();
        $siteFiles = $siteFiles->orderBy('placement_date', 'desc')->paginate(15);
        $mvsFiles = $mvsFiles->orderBy('placement_date', 'desc')->paginate(15);

        $stnbyFiles = [];
        try {
            $stnbyData = $stnbyService->getProjectsWithFiles();
            if ($stnbyData['success'] ?? false) {
                $stnbyFiles = $stnbyData['files'] ?? [];
                
                if ($request->filled('name')) {
                    $searchName = $request->name;
                    $stnbyFiles = array_filter($stnbyFiles, function($file) use ($searchName) {
                        return stripos($file['project_name'] ?? '', $searchName) !== false 
                            || stripos($file['filename'] ?? '', $searchName) !== false
                            || stripos($file['project_code'] ?? '', $searchName) !== false;
                    });
                }
                
                if ($request->filled('purpose') && $request->purpose !== '') {
                    $purpose = $request->purpose;
                    if ($purpose !== 'На сайт') {
                        $stnbyFiles = [];
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Ошибка получения данных с stn.by', [
                'error' => $e->getMessage(),
            ]);
        }

        $stnbyFilesFormatted = collect($stnbyFiles)->map(function($file) {
            return [
                'id' => 'stnby_' . $file['file_id'],
                'date' => $file['posted'] ?? $file['project_date_put'] ?? '',
                'type' => $file['filetype_name'] ?? 'Неизвестно',
                'name' => $file['project_name'] ?? 'Неизвестно',
                'purpose' => 'На сайт',
                'author' => 'Администратор',
                'source' => 'stnby',
                'project_code' => $file['project_code'] ?? '',
                'filename' => $file['filename'] ?? '',
                'download_url' => $file['download_url'] ?? null,
                'file_size' => $file['file_size'] ?? 0,
            ];
        });

        $allUploadedFiles = $uploadedFilesLocal->map(function($file) {
            $date = $file->date;
            if ($date instanceof \Carbon\Carbon) {
                $date = $date->format('Y-m-d');
            } elseif ($date) {
                $date = is_string($date) ? $date : (string) $date;
            } else {
                $date = '';
            }
            
            return [
                'id' => $file->id,
                'date' => $date,
                'type' => $file->type,
                'name' => $file->name,
                'purpose' => $file->purpose,
                'author' => $file->author,
                'source' => 'local',
            ];
        })->concat($stnbyFilesFormatted);

        $allUploadedFiles = $allUploadedFiles->sortByDesc('date')->values();

        $uploadedFilesPaginated = [
            'data' => $allUploadedFiles->toArray(),
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => $allUploadedFiles->count(),
            'total' => $allUploadedFiles->count(),
            'links' => [],
        ];

        return Inertia::render('Files', [
            'activeTab' => $activeTab,
            'uploadedFiles' => $uploadedFilesPaginated,
            'siteFiles' => $siteFiles,
            'mvsFiles' => $mvsFiles,
            'filters' => $request->only(['name', 'purpose'])
        ]);
    }

    /**
     * Сохранить загруженный файл
     */
    public function storeUploadedFile(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'type' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'purpose' => 'required|string|max:255',
            'author' => 'required|string|max:255',
        ]);

        UploadedFile::create($request->all());

        return redirect()->back()->with('success', 'Файл успешно добавлен');
    }

    /**
     * Сохранить файл для размещения на сайте
     */
    public function storeSiteFile(Request $request)
    {
        $request->validate([
            'placement_date' => 'required|date',
            'file_type' => 'required|string|max:255',
            'development_name' => 'required|string|max:255',
        ]);

        SiteFile::create($request->all());

        return redirect()->back()->with('success', 'Файл успешно добавлен на сайт');
    }

    /**
     * Сохранить файл для МВС
     */
    public function storeMvsFile(Request $request)
    {
        $request->validate([
            'placement_date' => 'required|date',
            'development_name' => 'required|string|max:255',
        ]);

        MvsFile::create($request->all());

        return redirect()->back()->with('success', 'Файл успешно добавлен на МВС');
    }

    /**
     * Удалить загруженный файл
     */
    public function destroyUploadedFile(UploadedFile $uploadedFile)
    {
        $uploadedFile->delete();
        return redirect()->back()->with('success', 'Файл успешно удален');
    }

    /**
     * Удалить файл с сайта
     */
    public function destroySiteFile(SiteFile $siteFile)
    {
        $siteFile->delete();
        return redirect()->back()->with('success', 'Файл успешно удален');
    }

    /**
     * Удалить файл с МВС
     */
    public function destroyMvsFile(MvsFile $mvsFile)
    {
        $mvsFile->delete();
        return redirect()->back()->with('success', 'Файл успешно удален');
    }

    /**
     * Получить список проектов с stn.by для выбора
     */
    public function getStnbyProjects(Request $request, StnbyApiService $stnbyService)
    {
        $request->user()->can('files.upload') || abort(403);

        $projects = $stnbyService->getProjects();
        
        return response()->json($projects);
    }

    /**
     * Получить список типов файлов с stn.by
     */
    public function getStnbyFileTypes(Request $request, StnbyApiService $stnbyService)
    {
        $request->user()->can('files.upload') || abort(403);

        $fileTypes = $stnbyService->getFileTypes();
        
        return response()->json($fileTypes);
    }

    /**
     * Загрузить файл на stn.by
     */
    public function uploadToStnby(Request $request, StnbyApiService $stnbyService)
    {
        $request->user()->can('files.upload') || abort(403);

        $request->validate([
            'project_id' => 'required|integer',
            'file_type' => 'required|integer',
            'file' => 'required|file|mimes:pdf|max:10240', // 10MB
        ]);

        try {
            $result = $stnbyService->uploadFile(
                $request->project_id,
                $request->file_type,
                $request->file('file')
            );

            if ($result['success'] ?? false) {
                return redirect()->back()->with('success', 'Файл успешно загружен на stn.by');
            }

            return redirect()->back()->with('error', 
                'Ошибка загрузки: ' . ($result['error'] ?? 'Неизвестная ошибка'));
        } catch (\Exception $e) {
            \Log::error('Ошибка загрузки файла на stn.by', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 
                'Ошибка: ' . $e->getMessage());
        }
    }
}
