<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApprovalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

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

        Route::patch('/admin/attendances/{id}', [AdminController::class, 'updateDetail'])->name('admin.updateDetail');

        // 管理者承認系
        Route::get('/admin/requests', [ApprovalController::class, 'indexRequests'])->name('approval.indexRequests');

        Route::get('/admin/requests/{id}', [ApprovalController::class, 'showRequest'])->name('approval.showRequest');

        Route::patch('/admin/requests/{id}', [ApprovalController::class, 'updateRequest'])->name('approval.updateRequest');

        Route::get('/admin/users', [AdminController::class, 'indexUsers'])->name('admin.indexUsers');

        Route::get('/admin/users/{user}/attendances', [AdminController::class, 'userAttendances'])->name('admin.userAttendances');

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


// Fortifyに干渉しない管理者専用ログイン処理
Route::post('/admin/login', function (Request $request) {
    // 入力チェック
    $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ], [
        'email.required' => 'メールアドレスを入力してください',
        'password.required' => 'パスワードを入力してください',
    ]);

    // 管理者の取得
    $user = User::where('email', $request->email)->first();

    // 管理者判定＆パスワード一致確認
    if (! $user || ! $user->admin_role || ! Hash::check($request->password, $user->password)) {
        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ])->withInput();
    }

    // ログイン成功
    Auth::login($user);
    return redirect()->route('admin.index');
});
