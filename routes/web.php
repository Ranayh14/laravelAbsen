<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\PageController;

// Intercept ajax requests to the old ajax_handler.php or index.php?ajax=...
Route::any('/ajax_handler.php', [PageController::class, 'page']);
Route::any('/index.php', [PageController::class, 'index']);

use App\Http\Controllers\Web\ExportController;

// Export Routes
Route::prefix('export')->group(function () {
    Route::get('/kpi', [ExportController::class, 'exportKpi'])->name('export.kpi');
    Route::get('/daily', [ExportController::class, 'exportDaily'])->name('export.daily');
    Route::get('/monthly', [ExportController::class, 'exportMonthly'])->name('export.monthly');
});

Route::any('/', [PageController::class, 'index'])->name('home');
Route::any('/{page}', [PageController::class, 'page'])->where('page', '.*');
