@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user/detail.css')}}">
@endsection

@section('content')
<div class="container">
    <h1 class="page-title">勤怠詳細</h1>

    <form class="detail-form" action="{{ route('user.updateDetail', ['id' => $attendance->id]) }}" method="POST">
        @csrf
        @method('PATCH')
<!-- 名前 （表示のみ）-->
        <div class="detail-form__input-group">
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

                <div class="time-input-wrapper">
                    <div class="time-input-row">
                        <input class="detail-form__input" type="time" name="clock_in" value="{{ old('clock_in', $clock_in ?? '') }}" {{ $isLocked ? 'disabled' : '' }}>             
                        <span>～</span>
                        <input class="detail-form__input" type="time" name="clock_out" value="{{ old('clock_out', $clock_out ?? '') }}" {{ $isLocked ? 'disabled' : '' }}>
                    </div>
                    <div class="error-messages">
                        @error('clock_in')
                            {{ $message }}
                        @enderror
                        <!-- @error('clock_out')
                            {{ $message }}
                        @enderror -->
                    </div>
                </div>
            </div>

<!-- 休憩１（表示と修正） -->
            <div class="detail-form__item">
                <p class="detail-form__label">休憩</p>

                <div class="time-input-wrapper">
                    <div class="time-input-row">
                        <input class="detail-form__input" type="time" name="break1_start" value="{{ old('break1_start', $break1_start ?? '') }}" {{ $isLocked ? 'disabled' : '' }}>
                        <span>～</span>
                        <input class="detail-form__input" type="time" name="break1_end" value="{{ old('break1_end', $break1_end ?? '') }}" {{ $isLocked ? 'disabled' : '' }}>
                    </div>
                    <div class="error-messages">
                        @error('break1_start') 
                            {{ $message }}
                        @enderror
                        @error('break1_end')
                        {{ $message }}
                    @enderror
                    </div>
                </div>
            </div>

<!-- 休憩２（表示と修正） -->
            <div class="detail-form__item">
                <p class="detail-form__label">休憩２</p>

                <div class="time-input-wrapper">
                    <div class="time-input-row">
                        <input class="detail-form__input" type="time" name="break2_start" value="{{ old('break2_start', $break2_start ?? '') }}" {{ $isLocked ? 'disabled' : '' }}>
                        <span>～</span>
                        <input class="detail-form__input" type="time" name="break2_end" value="{{ old('break2_end', $break2_end ?? '') }}" {{ $isLocked ? 'disabled' : '' }}>
                    </div>
                    <div class="error-messages">
                        @error('break2_start') 
                            {{ $message }}
                        @enderror
                        @error('break2_end')
                            {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>

<!-- 備考（表示と修正） -->
            <div class="detail-form__item">
                <p class="detail-form__label">備考</p>

                <div class="time-input-wrapper">
                    <div class="time-input-row">
                        <textarea class="detail-form__textarea" name="note" {{ $isLocked ? 'readonly' : '' }}>{{ old('note', $attendance->note ?? '') }}</textarea>
                    </div>
                        <div class="error-messages">
                        @error('note')
                            {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>
            @if ($isLocked)
                <div class="locked-wrapper">
                    <p class="locked-message">※　承認待ちのため修正はできません。</p>
                </div>
            @else
                <div class="detail-form__button-group">
                    <button class="detail-form__button" type="submit">修 正</button>
                </div>
            @endif
        </div>
        
    </form>
</div>
@endsection