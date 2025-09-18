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

        // LoginRequest のバリデーションを実行する
        // Fortify::authenticateUsing(function ($request) {
        //     $request->validate(); 
        
        //     $user = Auth::getProvider()->retrieveByCredentials([
        //         'email' => $request->email,
        //         'password' => $request->password,
        //     ]);

        //     if ($user && Auth::validate(['email' => $request->email, 'password' => $request->password])) {
        //     return $user;
        //     }

        //     return null;
        // });
        // Fortify::authenticateUsing(function ($request) {
        //     $customRequest = CustomLoginRequest::createFrom($request->toArray());
        //     $customRequest->validate();
            
        //     $user = User::where('email', $request->email)->first();

        //     if ($user && Hash::check($request->password, $user->password)) {
            
        //     return $user;
        //     }
        // });

        Fortify::authenticateUsing(function (LoginRequest $request) {
            
            $request->validated();
            // ユーザーを検索
            $user = User::where('email', $request->email)->first();

            // パスワードを確認
            if (!$user || !Hash::check($request->password, $user->password)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'email' => ['ログイン情報が登録されていません'],       
                ]);
            }
            return $user;
        });

        // ログインフォームのバリデーション
        $this->app->bind(
            FortifyLoginRequest::class,
            LoginRequest::class
        );

    }
}
