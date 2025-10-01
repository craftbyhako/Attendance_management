@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user/detail.css')}}">
@endsection

@section('content')
<div class="content">
    <h1 class="page-title">勤怠詳細</h1>

    <form action="{{ route('user.showDetail', ['id' => $attendance->id]) }}" method="POST">
        @csrf
        @method('PATCH')
<!-- 名前 （表示のみ）-->
        <p class="detail-form__label">名前</p>
        <div class="detail-form__input">
            {{ $user->user_name }}
        </div>

<!-- 日付（表示のみ） -->
        <p class="detail-form__label" >日付</p>
        <div class="detail-form__input">
            {{ $targetDate }}
        </div>

<!-- 出勤・退勤時間（表示と修正） -->
        <p class="detail-form__label" >出勤・退勤</p>
        <input class="detail-form__input" type="time"  value="{{ $clock_in ?? '' }}">
        <span>～</span>
        <input class="detail-form__input" type="time" value="{{ $clock_out ?? '' }}">
        <div class="error-message">
            @error('clock_in') 
                {{ $message }}
            @enderror
            @error('clock_out')
                {{ $message }}
            @enderror
        </div>

<!-- 休憩１（表示と修正） -->
        <p class="detail-form__label">休憩</p>
        <input class="detail-form__input" type="time" value="{{ $break1_start ?? '' }}">
        <span>～</span>
        <input class="detail-form__input" type="time" value="{{ $break1_end ?? '' }}">

<!-- 休憩２（表示と修正） -->
        <p class="detail-form__label">休憩２</p>
        <input class="detail-form__input" type="time" value="{{ $break2_start ?? '' }}">
        <span>～</span>
        <input class="detail-form__input" type="time" value="{{ $break2_end ?? '' }}">

<!-- 備考（表示と修正） -->
        <p class="detail-form__label">備考</p>
        <input class="detail-form__input" type="text" value="{{ $attendance->note }}">
        <div class="error-message">
            @error('note')
                {{ $message }}
            @enderror
        </div>

        <button class="detail-form__button" type="submit">修正</button>
    </form>



</div>
@endsection