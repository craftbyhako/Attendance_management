@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css')}}">
@endsection

@section('content')
<div class="content">
    <form class="" action="" method="">
        @csrf
        <h1 class="page-title">会員登録</h1>
            <label class="register-form__label" for="user_name"></label>

    </form>

</div>