<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StnbyApiService
{
    protected string $apiUrl;
    protected string $apiKey;
    protected int $cacheTtl;

    public function __construct()
    {
        $this->apiUrl = config('services.stnby.api_url', 'http://stn.by/admin/api');
        $this->apiKey = config('services.stnby.api_key');
        $this->cacheTtl = config('services.stnby.cache_ttl', 600);
        
        if (empty($this->apiKey)) {
            Log::warning('STNBY API Key is not set in config');
        }
    }

    /**
     * Получить список активных проектов с файлами
     */
    public function getProjectsWithFiles(array $params = []): array
    {
        if (empty($this->apiKey)) {
            Log::warning('STNBY API: API key is not configured');
            return [
                'success' => false,
                'error' => 'API ключ не настроен',
                'files' => [],
            ];
        }

        $cacheKey = 'stnby_projects_files_' . md5(json_encode($params));
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($params) {
            try {
                $projectsResponse = Http::timeout(30)
                    ->get($this->apiUrl . '/get_projects.php', array_merge([
                        'api_key' => $this->apiKey,
                        'show_all' => '0',
                    ], $params));

                if (!$projectsResponse->successful()) {
                    Log::error('STNBY API Error: Failed to get projects', [
                        'status' => $projectsResponse->status(),
                        'body' => $projectsResponse->body(),
                    ]);
                    
                    return [
                        'success' => false,
                        'error' => 'Ошибка при получении проектов',
                        'files' => [],
                    ];
                }

                $projectsData = $projectsResponse->json();

                if (!$projectsData['success'] ?? false) {
                    Log::warning('STNBY API: Projects request failed', [
                        'error' => $projectsData['error'] ?? 'Unknown error',
                    ]);
                    
                    return [
                        'success' => false,
                        'error' => $projectsData['error'] ?? 'Ошибка получения проектов',
                        'files' => [],
                    ];
                }

                $allFiles = [];
                
                foreach ($projectsData['projects'] ?? [] as $project) {
                    $filesResponse = Http::timeout(30)
                        ->get($this->apiUrl . '/get_project_files.php', [
                            'api_key' => $this->apiKey,
                            'project_id' => $project['id'],
                        ]);

                    if ($filesResponse->successful()) {
                        $filesData = $filesResponse->json();
                        
                        if ($filesData['success'] ?? false) {
                            foreach ($filesData['files'] ?? [] as $file) {
                                $allFiles[] = [
                                    'project_id' => $project['id'],
                                    'project_code' => $project['code'],
                                    'project_name' => $project['name'],
                                    'project_date_put' => $project['date_put'],
                                    'project_stage' => $project['stage_name'] ?? null,
                                    'file_id' => $file['file_id'],
                                    'filename' => $file['filename'],
                                    'filetype_id' => $file['filetype_id'],
                                    'filetype_name' => $file['filetype_name'],
                                    'posted' => $file['posted'],
                                    'file_exists' => $file['file_exists'] ?? false,
                                    'download_url' => $file['download_url'] ?? null,
                                    'file_size' => $file['file_size'] ?? 0,
                                    'source' => 'stnby',
                                ];
                            }
                        }
                    }
                }

                return [
                    'success' => true,
                    'count' => count($allFiles),
                    'files' => $allFiles,
                ];
            } catch (\Exception $e) {
                Log::error('STNBY API Exception', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                
                return [
                    'success' => false,
                    'error' => 'Исключение при работе с API: ' . $e->getMessage(),
                    'files' => [],
                ];
            }
        });
    }

    /**
     * Получить список проектов (без файлов)
     */
    public function getProjects(array $params = []): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'error' => 'API ключ не настроен',
                'projects' => [],
            ];
        }

        $cacheKey = 'stnby_projects_' . md5(json_encode($params));
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($params) {
            try {
                $response = Http::timeout(30)
                    ->get($this->apiUrl . '/get_projects.php', array_merge([
                        'api_key' => $this->apiKey,
                        'show_all' => '0',
                    ], $params));

                if (!$response->successful()) {
                    Log::error('STNBY API Error: Failed to get projects', [
                        'status' => $response->status(),
                    ]);
                    
                    return [
                        'success' => false,
                        'error' => 'Ошибка при получении проектов',
                        'projects' => [],
                    ];
                }

                $data = $response->json();
                return $data;
            } catch (\Exception $e) {
                Log::error('STNBY API Exception', [
                    'message' => $e->getMessage(),
                ]);
                
                return [
                    'success' => false,
                    'error' => 'Исключение при работе с API: ' . $e->getMessage(),
                    'projects' => [],
                ];
            }
        });
    }

    /**
     * Загрузить файл на stn.by
     */
    public function uploadFile(int $projectId, int $fileType, $file): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'error' => 'API ключ не настроен',
            ];
        }

        try {
            $filePath = is_string($file) ? $file : $file->getRealPath();
            $fileName = is_string($file) ? basename($file) : $file->getClientOriginalName();

            $response = Http::timeout(60)
                ->attach(
                    'file',
                    file_get_contents($filePath),
                    $fileName
                )
                ->post($this->apiUrl . '/upload_file.php', [
                    'api_key' => $this->apiKey,
                    'project_id' => $projectId,
                    'file_type' => $fileType,
                ]);

            if (!$response->successful()) {
                Log::error('STNBY Upload Error', [
                    'project_id' => $projectId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                
                return [
                    'success' => false,
                    'error' => 'Ошибка при загрузке файла',
                ];
            }

            $data = $response->json();

            if (!$data['success'] ?? false) {
                return [
                    'success' => false,
                    'error' => $data['error'] ?? 'Ошибка загрузки',
                ];
            }

            $this->clearProjectsCache();

            return $data;
        } catch (\Exception $e) {
            Log::error('STNBY Upload Exception', [
                'project_id' => $projectId,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'error' => 'Исключение при загрузке: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Получить список типов файлов
     */
    public function getFileTypes(): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'error' => 'API ключ не настроен',
                'filetypes' => [],
            ];
        }

        $cacheKey = 'stnby_filetypes';
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            try {
                $response = Http::timeout(30)
                    ->get($this->apiUrl . '/get_filetypes.php', [
                        'api_key' => $this->apiKey,
                    ]);

                if (!$response->successful()) {
                    Log::error('STNBY API Error: Failed to get file types', [
                        'status' => $response->status(),
                    ]);
                    
                    return [
                        'success' => false,
                        'error' => 'Ошибка при получении типов файлов',
                        'filetypes' => [],
                    ];
                }

                $data = $response->json();
                return $data;
            } catch (\Exception $e) {
                Log::error('STNBY API Exception', [
                    'message' => $e->getMessage(),
                ]);
                
                return [
                    'success' => false,
                    'error' => 'Исключение при работе с API: ' . $e->getMessage(),
                    'filetypes' => [],
                ];
            }
        });
    }

    /**
     * Очистить кеш проектов
     */
    public function clearProjectsCache(): void
    {
        Cache::flush();
    }

    /**
     * Очистить весь кеш
     */
    public function clearCache(): void
    {
        Cache::flush();
    }
}

