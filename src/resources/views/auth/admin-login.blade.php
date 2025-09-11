@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-login.css')}}">
@endsection

@section('content')
<div class="content">
    <form class="admin-login" action="/login" method="POST">
        @csrf
        <h1 class="page-title">ログイン</h1>

        <label class="admin-login__label" for="email">メールアドレス</label>
        <input class="admin-login__input" type="email" id="email" name="email" value="{{ old('email' )}}">
        <div class="form__error">
            @error('email')
                {{ $message }}
            @enderror
        </div>

        <label class="admin-login__label" for="password">パスワード</label>
        <input class="admin-login__input" type="password" id="password" name="password">
        <div class="form-error">
            @error('password')
                {{ $message }}
            @enderror
        </div>

        <button class="admin-login__button">ログインする</button>

        <a class="admin-login__link" href="/register">会員登録はこちら</a>
    </form>
</div>
@endsection