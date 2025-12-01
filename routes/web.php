<?php

use App\Http\Controllers\ActiveListController;
use App\Http\Controllers\PlannedListController;
use App\Http\Controllers\FilesController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\StagesController;
use App\Http\Controllers\CostController;
use App\Http\Controllers\ActivityLogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('active-list');
})->name('home');

Route::get('dashboard', function () {
    return redirect()->route('active-list');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('active-list', [ActiveListController::class, 'index'])->middleware(['auth', 'verified'])->name('active-list');
Route::post('active-list', [ActiveListController::class, 'store'])->middleware(['auth', 'verified']);
Route::put('active-list/{activeList}', [ActiveListController::class, 'update'])->middleware(['auth', 'verified']);
Route::delete('active-list/{activeList}', [ActiveListController::class, 'destroy'])->middleware(['auth', 'verified']);

Route::get('planned-list', [PlannedListController::class, 'index'])->middleware(['auth', 'verified'])->name('planned-list');
Route::post('planned-list', [PlannedListController::class, 'store'])->middleware(['auth', 'verified']);
Route::put('planned-list/{plannedList}', [PlannedListController::class, 'update'])->middleware(['auth', 'verified']);
Route::delete('planned-list/{plannedList}', [PlannedListController::class, 'destroy'])->middleware(['auth', 'verified']);
Route::post('planned-list/approve', [PlannedListController::class, 'approve'])->middleware(['auth', 'verified'])->name('planned-list.approve');
Route::get('planned-list/export', [PlannedListController::class, 'export'])->middleware(['auth', 'verified'])->name('planned-list.export');
Route::get('planned-list/preview', [PlannedListController::class, 'preview'])->middleware(['auth', 'verified'])->name('planned-list.preview');
Route::get('planned-list/next-code', [PlannedListController::class, 'nextCode'])->middleware(['auth', 'verified'])->name('planned-list.next-code');

Route::get('files', [FilesController::class, 'index'])->middleware(['auth', 'verified'])->name('files');
Route::post('files/uploaded', [FilesController::class, 'storeUploadedFile'])->middleware(['auth', 'verified']);
Route::post('files/site', [FilesController::class, 'storeSiteFile'])->middleware(['auth', 'verified']);
Route::post('files/mvs', [FilesController::class, 'storeMvsFile'])->middleware(['auth', 'verified']);
Route::delete('files/uploaded/{uploadedFile}', [FilesController::class, 'destroyUploadedFile'])->middleware(['auth', 'verified']);
Route::delete('files/site/{siteFile}', [FilesController::class, 'destroySiteFile'])->middleware(['auth', 'verified']);
Route::delete('files/mvs/{mvsFile}', [FilesController::class, 'destroyMvsFile'])->middleware(['auth', 'verified']);
Route::get('files/stnby/projects', [FilesController::class, 'getStnbyProjects'])->middleware(['auth', 'verified']);
Route::get('files/stnby/filetypes', [FilesController::class, 'getStnbyFileTypes'])->middleware(['auth', 'verified']);
Route::post('files/stnby/upload', [FilesController::class, 'uploadToStnby'])->middleware(['auth', 'verified']);

Route::get('reports', [ReportsController::class, 'index'])->middleware(['auth', 'verified'])->name('reports');
Route::post('reports', [ReportsController::class, 'store'])->middleware(['auth', 'verified']);
Route::put('reports/{report}', [ReportsController::class, 'update'])->middleware(['auth', 'verified']);
Route::delete('reports/{report}', [ReportsController::class, 'destroy'])->middleware(['auth', 'verified']);
Route::post('reports/calendar', [ReportsController::class, 'storeCalendarPlan'])->middleware(['auth', 'verified']);
Route::put('reports/calendar/{calendarPlan}', [ReportsController::class, 'updateCalendarPlan'])->middleware(['auth', 'verified']);
Route::delete('reports/calendar/{calendarPlan}', [ReportsController::class, 'destroyCalendarPlan'])->middleware(['auth', 'verified']);

Route::get('archive', [ArchiveController::class, 'index'])->middleware(['auth', 'verified'])->name('archive');
Route::post('archive', [ArchiveController::class, 'store'])->middleware(['auth', 'verified']);
Route::put('archive/{archive}', [ArchiveController::class, 'update'])->middleware(['auth', 'verified']);
Route::delete('archive/{archive}', [ArchiveController::class, 'destroy'])->middleware(['auth', 'verified']);

Route::get('cost', [CostController::class, 'index'])->middleware(['auth', 'verified'])->name('cost');
Route::post('cost', [CostController::class, 'store'])->middleware(['auth', 'verified']);
Route::put('cost/{cost}', [CostController::class, 'update'])->middleware(['auth', 'verified']);
Route::delete('cost/{cost}', [CostController::class, 'destroy'])->middleware(['auth', 'verified']);

Route::get('users', [UsersController::class, 'index'])->middleware(['auth', 'verified'])->name('users');
Route::post('users', [UsersController::class, 'store'])->middleware(['auth', 'verified']);
Route::put('users/{user}', [UsersController::class, 'update'])->middleware(['auth', 'verified']);
Route::delete('users/{user}', [UsersController::class, 'destroy'])->middleware(['auth', 'verified']);

Route::get('stages', [StagesController::class, 'index'])->middleware(['auth', 'verified'])->name('stages');
Route::post('stages', [StagesController::class, 'store'])->middleware(['auth', 'verified']);
Route::put('stages/{stage}', [StagesController::class, 'update'])->middleware(['auth', 'verified']);
Route::delete('stages/{stage}', [StagesController::class, 'destroy'])->middleware(['auth', 'verified']);

Route::get('activity-log', [ActivityLogController::class, 'index'])->middleware(['auth', 'verified'])->name('activity-log');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
