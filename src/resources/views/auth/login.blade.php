@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css')}}">
@endsection

@section('content')
<div class="content">
    <form class="login-form" action="/login" method="POST" novalidate>
        @csrf
        <h1 class="page-title">ログイン</h1>

        <label class="login-form__label" for="email">メールアドレス</label>
        <input class="login-form__input" type="email" id="email" name="email" value="{{ old('email' )}}">
        <div class="form__error">
            @error('email')
                {{ $message }}
            @enderror
        </div>

        <label class="login-form__label" for="password">パスワード</label>
        <input class="login-form__input" type="password" id="password" name="password">
        <div class="form__error">
            @error('password')
                {{ $message }}
            @enderror
        </div>

        <button class="login__button" type="submit">ログインする</button>

        <a class="login__link" href="/register">会員登録はこちら</a>
    </form>
</div>
@endsection