
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TravelerDeclarationController;
use App\Http\Controllers\Admin\DeclarationReportController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('suite', [DashboardController::class, 'index'])
        ->name('admin.home');

    // Administração de utilizadores
    Route::middleware(['Administrador'])->group(function () {

        Route::get('admin/user/index', [UserController::class, 'index'])
            ->name('admin.user.index');

        Route::get('admin/user/show/{uuid}', [UserController::class, 'show'])
            ->name('admin.user.show')
            ->withoutMiddleware(['Administrador']);

        Route::get('admin/user/edit/{uuid}', [UserController::class, 'edit'])
            ->name('admin.user.edit')
            ->withoutMiddleware(['Administrador']);

        Route::put('admin/user/update/{uuid}', [UserController::class, 'update'])
            ->name('admin.user.update')
            ->withoutMiddleware(['Administrador']);

        Route::put('admin/user/password/{uuid}', [UserController::class, 'updatePassword'])
            ->name('admin.user.password')
            ->withoutMiddleware(['Administrador']);

        Route::get('admin/user/delete/{uuid}', [UserController::class, 'destroy'])
            ->name('admin.user.delete');
    });

    // Gestão das declarações de saúde
    Route::middleware(['technical'])->group(function () {

        Route::get('admin/declaration/index', [TravelerDeclarationController::class, 'index'])
            ->name('admin.declaration.index');

        Route::get('admin/declaration/travelers', [TravelerDeclarationController::class, 'travelers'])
            ->name('admin.declaration.travelers');

        Route::get('admin/declaration/travelers/{passport}', [TravelerDeclarationController::class, 'traveler'])
            ->name('admin.declaration.traveler');

        Route::get('admin/declaration/show/{id}', [TravelerDeclarationController::class, 'show'])
            ->name('admin.declaration.show');

        Route::get('admin/declaration/edit/{id}', [TravelerDeclarationController::class, 'edit'])
            ->name('admin.declaration.edit');

        Route::put('admin/declaration/update/{id}', [TravelerDeclarationController::class, 'update'])
            ->name('admin.declaration.update');

        Route::post('admin/declaration/status/{id}', [TravelerDeclarationController::class, 'Alterstatus'])
            ->name('admin.declaration.status');

        Route::get('admin/declaration/{id}/qrcode', [TravelerDeclarationController::class, 'qrcode'])
            ->name('admin.declaration.qrcode');

        // Relatórios
        Route::post('admin/declaration/report/pdf', [DeclarationReportController::class, 'pdf'])
            ->name('admin.declaration.report.pdf');

        Route::post('admin/declaration/report/excel', [DeclarationReportController::class, 'excel'])
            ->name('admin.declaration.report.excel');
    });
});
