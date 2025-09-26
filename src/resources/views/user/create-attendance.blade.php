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
                
                
                @if ($attendance->attendance_status_id === 1)
                    <button class="create__button--black" type="submit" name="action" value="clock_in">出 勤</button>

            @elseif($attendance->attendance_status_id === 4)
            <!-- 退勤を押した時 -->
                <p class="create__item--status">退勤済</p>
                <div class="create__item--date">
                    {{ \Carbon\Carbon::now()->locale('ja')->isoFormat('YYYY年M月D日（ddd）') }}
                </div>

                <div class="create__item--time">
                    {{ \Carbon\Carbon::now()->format('H:i') }}
                </div>      

                <div class="create__message">お疲れ様でした。</div>

            @elseif ($attendance->attendance_status_id === 3)
             <!-- 休憩中の時 -->
                <p class="create__item--status">休憩中</p>

                <div class="create__item--date">
                    {{ \Carbon\Carbon::now()->locale('ja')->isoFormat('YYYY年M月D日（ddd）') }}
                </div>

                <div class="create__item--time">
                    {{ \Carbon\Carbon::now()->format('H:i') }}
                </div>         
                
                <button class="create__button--white" type="submit" name="action" value="break1_end">休 憩 戻</button>

            

            @else
            <!-- 勤務中の時 -->
                <p class="create__item--status">出勤中</p>

                <div class="create__item--date">
                    {{ \Carbon\Carbon::now()->locale('ja')->isoFormat('YYYY年M月D日（ddd）') }}
                </div>

                <div class="create__item--time">
                    {{ \Carbon\Carbon::now()->format('H:i') }}
                </div>         
                
                <div class="button-group">
                    <button class="create__button--black" type="submit" name="action" value="clock_out">退 勤</button>
                    <button class="create__button--white" type="submit" name="action" value="break1_start">休 憩 入</button>
                </div>
            @endif
        @else
            <p class="create__item--status">まだ勤怠記録はありません</p>
            <div class="create__item--date">
                    {{ \Carbon\Carbon::now()->locale('ja')->isoFormat('YYYY年M月D日（ddd）') }}
                </div>

                <div class="create__item--time">
                    {{ \Carbon\Carbon::now()->format('H:i') }}
                </div>      
            <button class="create__button--black" type="submit" name="action" value="clock_in">出 勤</button>
        @endif
    </form>
</div>
@endsection