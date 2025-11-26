<?php

use Illuminate\Support\Facades\Route;
use Modules\Audit\Http\Controllers\AuditController;
use Modules\Audit\Http\Controllers\AuditTemplateController;
use Modules\Audit\Http\Controllers\AuditSettingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => 'auth', 'prefix' => 'account'], function () {

    // Audit Templates
    Route::prefix('audit')->group(function () {
        Route::resource('audit-templates', AuditTemplateController::class);
        Route::get('audit-templates/department/{departmentId}', [AuditTemplateController::class, 'getByDepartment'])
            ->name('audit-templates.by-department');
    });

    // Audits
    Route::resource('audits', AuditController::class)->except(['edit', 'update']);

    // Audit Execution
    Route::get('audits/{audit}/execute', [AuditController::class, 'execute'])->name('audits.execute');
    Route::post('audits/{audit}/checkpoint/{response}', [AuditController::class, 'updateCheckpointResponse'])
        ->name('audits.update-checkpoint');
    Route::post('audits/{audit}/complete', [AuditController::class, 'complete'])->name('audits.complete');
    Route::post('audits/{audit}/cancel', [AuditController::class, 'cancel'])->name('audits.cancel');

    // Audit Files
    Route::delete('audits/{audit}/files/{file}', [AuditController::class, 'deleteFile'])->name('audits.delete-file');

    // Audit Export
    Route::get('audits/{audit}/export-pdf', [AuditController::class, 'exportPdf'])->name('audits.export-pdf');

    // My Audits (for auditees)
    Route::get('my-audits', [AuditController::class, 'myAudits'])->name('audits.my-audits');

    // Helper routes
    Route::get('audits/employees/department/{departmentId}', [AuditController::class, 'getEmployeesByDepartment'])
        ->name('audits.employees-by-department');

    // Audit Settings
    Route::get('audit-settings', [AuditSettingController::class, 'index'])->name('audit-settings.index');
    Route::post('audit-settings', [AuditSettingController::class, 'update'])->name('audit-settings.update');
});

