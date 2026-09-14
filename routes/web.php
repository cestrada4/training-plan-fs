<?php

use App\Http\Controllers\PayrollExportController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

Route::get('/time-cards/export-period-totals-old', [PayrollExportController::class, 'exportPeriodTotalsOld'])->name('payrollexport.export-period-totals-old');
Route::get('/time-cards/export-period-totals', [PayrollExportController::class, 'exportPeriodTotals'])->name('payrollexport.export-period-totals');

require __DIR__.'/settings.php';
