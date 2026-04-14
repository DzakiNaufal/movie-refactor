<?php

use App\Http\Controllers\MovieController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Homepage with search
Route::get('/', [MovieController::class, 'index'])->name('movies.index');

// RESTful Movie Resource Routes
Route::resource('movies', MovieController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

// Custom routes for data listing (for compatibility)
Route::get('/movies/data', [MovieController::class, 'data'])->name('movies.data');
Route::get('/movie/{id}', [MovieController::class, 'detail'])->name('movies.detail');
