<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

Route::middleware(['auth'])->group(function() {
    Route::get('/attendance', [UserController::class, 'create'])
        ->name('user.create');

    Route::post('/attendance', [UserController::class, 'store'])->name('user.store');

    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login'); // ログアウト後は login
    })->name('logout');

    Route::get('/attendance/list', [UserController::class, 'index'])->name('user.index');

    Route::get('/attendance/detail/{id}', [UserController::class, 'showDetail'])->name('user.showDetail');

    Route::patch('/attendance/detail/{id}', [UserController::class, 'updateDetail'])->name('user.updateDetail');

    Route::get('/stamp_correction_request/list', [UserController::class, 'indexUpdated'])->name('user.indexUpdated');
});

