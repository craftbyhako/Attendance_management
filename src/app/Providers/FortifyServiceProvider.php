<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use App\Http\Requests\LoginRequest;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Laravel\Fortify\Contracts\LoginResponse;
use Illuminate\Validation\ValidationException;




class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        
        //追記
        Fortify::registerView(function () {
            return view('auth.register');
        });


        Fortify::loginView(function () {
            if (request()->is('admin/login')) {
                return view('auth.admin-login');
            }
            return view('auth.login');
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });
        //追記ここまで



        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        $this->app->singleton(RegisterResponse::class, function () {
            return new class implements RegisterResponse 
            {
                public function toResponse($request)
                {
                    return redirect('/attendance');
                } 
            };
        });

         $this->app->singleton(LoginResponse::class, function () {
            return new class implements LoginResponse 
            {
                public function toResponse($request)
                {
                    return redirect('/attendance');
                } 
            };
        });


        Fortify::authenticateUsing(function (LoginRequest $request) {
            
            $request->validated();
            // ユーザーを検索
            $user = User::where('email', $request->email)->first();

            // ユーザーデータ、パスワードを確認
            if (!$user || !Hash::check($request->password, $user->password)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'email' => ['ログイン情報が登録されていません'],       
                ]);
            }

             // 管理者ページの場合、admin_role が 1 でなければログイン不可
            if (request()->is('admin/*') && $user->admin_role != 1) {
                throw ValidationException::withMessages([
                'email' => ['ログイン情報が登録されていません'],
                ]);
            }

            // 現在アクセスしているURLで判定
            // if (request()->is('admin/*')) {
            //     if ($user->admin_role != 1){
            //         throw ValidationException::withMessages([
            //         'email' => ['管理者専用のログインです'],
            //         ]);
            //     }
            // } else {
            //     if ($user->admin_role != 0 ){
            //         throw ValidationException::withMessages([
            //             'email' => ['一般ユーザー専用のログインです'],
            //         ]);
            //     }
            // }
            return $user;
        });

        // ログインフォームのバリデーション
        $this->app->bind(
            FortifyLoginRequest::class,
            LoginRequest::class
        );

        // ログイン後のリダイレクト先を分岐
        $this->app->singleton(LoginResponse::class, function() {
            return new class implements LoginResponse {
                public function toResponse ($request)
                {
                    if (auth()->user()->admin_role == 1) {
                        return redirect()->route('admin.index');
                    }    
                    return redirect()->route('user.create');
                }
            };
        });
    }
}