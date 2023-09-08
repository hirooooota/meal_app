<?php

use App\Http\Controllers\MealController;
use App\Http\Controllers\ProfileController;
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

Route::get('/', [MealController::class, 'index'])
    ->name('root');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::resource('meals', MealController::class)
    ->only(['create', 'store', 'edit', 'update', 'update', 'destroy'])
    ->middleware('auth');

Route::resource('meals', MealController::class)
    ->only(['show', 'index']);

Route::post('/meals/{meal}/like', [MealController::class, 'like'])->name('meals.like');

Route::delete('/meals/{meal}/unlike', [MealController::class, 'unlike'])->name('meals.unlike');

require __DIR__ . '/auth.php';
