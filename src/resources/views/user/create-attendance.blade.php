@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/create-attendance.css')}}">
@endsection

@section('content')
<div class="container">
    

    <form class="create__form" action="{{ route('user.store') }}" method="POST" >
        @csrf
        
        @if($attendance)
            @if($attendance->attendance_status_id === 1)
            <!-- 出勤前の時 -->
                <p class="create__item--status">勤務外</p>

                <div class="create__item--date">
                    {{ \Carbon\Carbon::now()->locale('ja')->isoFormat('YYYY年M月D日（ddd）') }}
                </div>

                <div class="create__item--time">
                    {{ \Carbon\Carbon::now()->format('H:i') }}
                </div>         
                
                <button type="submit" name="action" value="clock_in">出勤（＝勤務外）</button>

            @elseif ($attendance->attendance_status_id === 3)
             <!-- 休憩中の時 -->
                <p class="create__item--status">休憩中</p>

                <div class="create__item--date">
                    {{ \Carbon\Carbon::now()->locale('ja')->isoFormat('YYYY年M月D日（ddd）') }}
                </div>

                <div class="create__item--time">
                    {{ \Carbon\Carbon::now()->format('H:i') }}
                </div>         
                
                <button type="submit" name="action" value="break1_end">休憩戻（＝休憩中）</button>

            @elseif($attendance->attendance_status_id === 4)
            <!-- 退勤を押した時 -->
                <p class="create__item--status">退勤済</p>

                <div class="create__item--date">
                    {{ \Carbon\Carbon::now()->locale('ja')->isoFormat('YYYY年M月D日（ddd）') }}
                </div>

                <div class="create__item--time">
                    {{ \Carbon\Carbon::now()->format('H:i') }}
                </div>      

                <div>お疲れ様でした。（＝退勤済）</div>


            @else
            <!-- 勤務中の時 -->
                <p class="create__item--status">出勤中</p>

                <div class="create__item--date">
                    {{ \Carbon\Carbon::now()->locale('ja')->isoFormat('YYYY年M月D日（ddd）') }}
                </div>

                <div class="create__item--time">
                    {{ \Carbon\Carbon::now()->format('H:i') }}
                </div>         
                
                <button type="submit" name="action" value="clock_out">退勤（＝出勤中）</button>
                <button type="submit" name="action" value="break1_start">休憩入（＝出勤中）</button>
            @endif
        @else
            <p class="create__item--status">まだ勤怠記録はありません</p>
            <div class="create__item--date">
                    {{ \Carbon\Carbon::now()->locale('ja')->isoFormat('YYYY年M月D日（ddd）') }}
                </div>

                <div class="create__item--time">
                    {{ \Carbon\Carbon::now()->format('H:i') }}
                </div>      
            <button type="submit" name="action" value="clock_in">出勤</button>
        @endif
    </form>
</div>
@endsection