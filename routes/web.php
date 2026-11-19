
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Site\BlueEconomyController;
use App\Http\Controllers\Site\HackhatonSpatialStartups;
use App\Http\Controllers\Site\SiteController;
use App\Http\Controllers\Site\TravelerDeclarationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

/** Traveller Health Declaration */

// Página inicial
Route::get('/', [SiteController::class, 'index'])
    ->name('site.home');


/** End Traveller Health Declaration */

// Rotas de autenticação e administração
require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
