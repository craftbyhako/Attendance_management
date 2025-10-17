@extends('layouts.admin-app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/stamp.css')}}">
@endsection

@section('content')


<div class="container">
    <h1 class="page-title">勤怠詳細</h1>

    <form class="detail-form" action="{{ route('approval.updateRequest', ['id' => $attendance->id]) }}" method="POST">
        @csrf
        @method('PATCH')

<!-- 名前 （表示のみ）-->
        <div class="detail-form__input-group">
            <div class="detail-form__item">
                <p class="detail-form__label">名前</p>
                <div class="detail-form__input">
                    {{ $attendance->user->user_name ?? '' }}
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
                <input class="detail-form__input" type="time" name="clock_in" value="{{ old('clock_in', trim($clock_in ?? '')) }}" {{ $isLocked ? 'readonly' : '' }}>
                <span>～</span>
                <input class="detail-form__input" type="time" name="clock_out" value="{{ trim(old('clock_out', $clock_out ?? '')) }}" {{ $isLocked ? 'readonly' : '' }}>

                <div class="error-messages">
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
                <input class="detail-form__input" type="time" name="break1_start" value="{{ old('break1_start', trim($break1_start ?? '')) }}" {{ $isLocked ? 'readonly' : '' }}>
                <span>～</span>
                <input class="detail-form__input" type="time" name="break1_end" value="{{ old('break1_end', trim($break1_end ?? '')) }}" {{ $isLocked ? 'readonly' : '' }}>

                <div class="error-messages">
                    @error('break1_start') 
                        {{ $message }}
                    @enderror
                    @error('break1_end')
                        {{ $message }}
                    @enderror
                </div>
            </div>

<!-- 休憩２（表示と修正） -->
            <div class="detail-form__item">
                <p class="detail-form__label">休憩２</p>
                <input class="detail-form__input" type="time" name="break2_start" value="{{ old('break2_start', trim($break2_start ?? '')) }}" {{ $isLocked ? 'readonly' : '' }}>                               
                <span>～</span>                               
                <input class="detail-form__input" type="time" name="break2_end" value="{{ old('break2_end', trim($break2_end ?? '')) }}" {{ $isLocked ? 'readonly' : '' }}>
                <div class="error-messages">
                    @error('break2_start') 
                        {{ $message }}
                    @enderror
                    @error('break2_end')
                        {{ $message }}
                    @enderror
                </div>
            </div>

<!-- 備考（表示と修正） -->
            <div class="detail-form__item">
                <p class="detail-form__label">備考</p>
                <textarea class="detail-form__textarea" name="note" {{ $isLocked ? 'readonly' : '' }}>{{ old('note', $attendance->note ?? '') }}</textarea>

                <div class="error-messages">
                    @error('note')
                        {{ $message }}
                    @enderror
                </div>
            </div>
        </div>
    
    @if ($canApprove)
        <div class="detail-form__button-group">
            <button class="detail-form__button" type="submit">承 認</button>
        </div>            
    @else
        <div class="locked-wrapper">
            <p class="locked-message">承 認 済 み</p>
        </div>
    @endif
    </form>
</div>
@endsection