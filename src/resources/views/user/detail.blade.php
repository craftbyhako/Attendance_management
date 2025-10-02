@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user/detail.css')}}">
@endsection

@section('content')
<div class="container">
    <h1 class="page-title">勤怠詳細</h1>

    <form class="detail-form" action="{{ route('user.showDetail', ['id' => $attendance->id]) }}" method="POST">
        @csrf
        @method('PATCH')
<!-- 名前 （表示のみ）-->
        <div class="detail-form__item">
            <p class="detail-form__label">名前</p>
            <div class="detail-form__input">
                {{ $user->user_name }}
            </div>
        </div>

<!-- 日付（表示のみ） -->
        <div class="detail-form__item">
            <p class="detail-form__label" >日付</p>
            <div class="detail-form__input">
                {{ $targetDate }}
            </div>
        </div>

<!-- 出勤・退勤時間（表示と修正） -->
        <div class="detail-form__item">
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
        </div>

<!-- 休憩１（表示と修正） -->
        <div class="detail-form__item">
            <p class="detail-form__label">休憩</p>
            <input class="detail-form__input" type="time" value="{{ $break1_start ?? '' }}">
            <span>～</span>
            <input class="detail-form__input" type="time" value="{{ $break1_end ?? '' }}">
        </div>

<!-- 休憩２（表示と修正） -->
        <div class="detail-form__item">
            <p class="detail-form__label">休憩２</p>
            <input class="detail-form__input" type="time" value="{{ $break2_start ?? '' }}">
            <span>～</span>
            <input class="detail-form__input" type="time" value="{{ $break2_end ?? '' }}">
        </div>

<!-- 備考（表示と修正） -->
        <div class="detail-form__item">
            <p class="detail-form__label">備考</p>
            <textarea class="detail-form__textarea" name="note">{{ $attendance->note }}</textarea>
            <div class="error-message">
                @error('note')
                    {{ $message }}
                @enderror
            </div>
        </div>
    </form>

    <div class="detail-form__button-wrapper">
        <form action="{{ route('user.updateDetail', ['id' => $attendance->id]) }}" method="POST">
            @csrf
            <!-- @PATCH -->
            <button class="detail-form__button" type="submit">修 正</button>
        </form>
    </div>
</div>
@endsection