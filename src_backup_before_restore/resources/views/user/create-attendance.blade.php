@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/create-attendance.css')}}">
@endsection

@section('content')
<div class="container">
    <form class="create__form" action="{{ route('user.store') }}" method="POST" >
        @csrf

                <p class="create__item--status">{{ $statusLabel }}</p>

                <div class="create__item--date">
                    {{ \Carbon\Carbon::now()->locale('ja')->isoFormat('YYYY年M月D日（ddd）') }}
                </div>

                <div class="create__item--time">
                    {{ \Carbon\Carbon::now()->format('H:i') }}
                </div>         
                
                <!-- 出勤前 -->
                @if ($attendance->attendance_status_id === 1)
                    <button class="create__button--black" type="submit" name="action" value="clock_in">出 勤</button>

                <!-- 退勤済 -->
                @elseif($attendance->attendance_status_id === 4)
                    <div class="create__message">お疲れ様でした。</div>

                <!-- 出勤中 -->
                @elseif ($attendance->attendance_status_id === 2)
                    <div class="button-group">
                        <button class="create__button--black" type="submit" name="action" value="clock_out">退 勤</button>
                        <button class="create__button--white" type="submit" name="action" value="{{ $breakAction }}">休 憩 入</button>    
                    </div>

                <!-- 休憩中 -->
                @elseif($attendance->attendance_status_id === 3)
                    <button class="create__button--white" type="submit" name="action" value="{{ $breakAction }}">休 憩 戻</button>
                @endif
    </form>
</div>
@endsection