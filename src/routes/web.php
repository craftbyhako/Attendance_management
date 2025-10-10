<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApprovalController;

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
    // 一般ユーザ用ルート
    Route::get('/attendance', [UserController::class, 'create'])->name('user.create');

    Route::post('/attendance', [UserController::class, 'store'])->name('user.store');

    Route::get('/attendance/list', [UserController::class, 'index'])->name('user.index');

    Route::get('/attendance/detail/{id}', [UserController::class, 'showDetail'])->name('user.showDetail');

    Route::patch('/attendance/detail/{id}', [UserController::class, 'updateDetail'])->name('user.updateDetail');

    Route::get('/stamp_correction_request/list', [UserController::class, 'indexUpdated'])->name('user.indexUpdated');
    

        // 管理者ログイン後
        Route::get('/admin/attendances', [AdminController::class, 'index'])->name('admin.index');

        Route::get('/admin/attendances/{id}', [AdminController::class, 'showDetail'])->name('admin.showDetail');

        // Route::patch('/admin/attendances/{id}', [ApprovalController::class, 'updateDetail'])->name('approval.updateDetail');

        // 管理者承認系
        Route::get('/admin/requests', [ApprovalController::class, 'indexUpdated'])->name('approval.indexUpdated');

        Route::get('/admin/attedance/{id}', [ApprovalController::class, 'showRequest'])->name('approval.showRequest');


});

// 共通ログアウト
Route::post('/logout', function (Request $request) {
    $isAdmin = Auth::check() && Auth::user()->admin_role;
        
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    if($isAdmin) {
        return redirect('/admin/login');
    }
    return redirect('/login');
})->name('logout');


// 管理者ログイン画面の表示
Route::get('/admin/login', function () {
    return view('auth.admin-login'); // ここで表示するBlade
})->name('admin.login');
