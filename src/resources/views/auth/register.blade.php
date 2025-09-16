@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css')}}">
@endsection

@section('content')
<div class="content">
    <form class="register-form" action="/register" method="POST">
        @csrf
        <h1 class="page-title">会員登録</h1>
            <label class="register-form__label" for="user_name">ユーザー名</label>
            <input class="register-form__input" type="text" id="user_name" name="user_name"value="{{ old('user_name')}}">
            <div class="form__error">
                @error('user_name')
                    {{ $message }}
                @enderror
            </div>

            <label class="register-form__label" for="email">メールアドレス</label>
            <input class="register-form__input" type="email" id="email" name="email" value="{{ old('email') }}">
            <div class="form__error">
                @error('email')
                    {{ $message }}
                @enderror
            </div>

            <label class="register-form__label" for="password">パスワード</label>
            <input class="register-form__input" type="password" id="password" name="password">
            <div class="form__error">
                @error('password')
                    {{ $message }}
                @enderror
            </div>

            <label class="register-form__label" for="password_confirm">パスワード確認</label>
            <input class="register-form__label" type="password" id="password_confirm" name="password_confirmation">
            
            <button class="register__button">登録する</button>

            <a class="register__link" href="/login">ログインはこちら</a>
    </form>
</div>
@endsection