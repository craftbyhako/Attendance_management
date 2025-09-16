@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/create-attendance.css')}}">
@endsection

@section('content')
<div class="content">
    
<!-- 勤務外・出勤中・休憩中・退勤済　※勤務外と退勤済の違いは？ -->
    <form action="{{ route('user.store') }}" method="POST" >
        @csrf
        
        @if($attendance->attendance_status_id === 1)
            <!-- 出勤前の時 -->
            <p>勤務外</p>

            <div class="create__item--date">
                {{ \Carbon\Carbon::now()->local('ja')->isoFormat('YYYY年M月D日（ddd）') }}
            </div>

            <div class="create__item--time">
                {{ \Carbon\Carbon::now()->format('H:i') }}
            </div>         
                
            <button type="submit" name="action" value="clock_in">出勤（＝勤務外）</button>

        @elseif ($attendance->attendance_status_id === 3)
             <!-- 休憩中の時 -->
             <p>休憩中</p>

            <div class="create__item--date">
                {{ \Carbon\Carbon::now()->local('ja')->isoFormat('YYYY年M月D日（ddd）') }}
            </div>

            <div class="create__item--time">
                {{ \Carbon\Carbon::now()->format('H:i') }}
            </div>         
                
            <button type="submit" name="action" value="break1_end">休憩戻（＝休憩中）</button>

        @elseif($attendance->attendance_status_id === 4)
            <!-- 退勤を押した時 -->
            <p>退勤済</p>

            <div class="create__item--date">
                {{ \Carbon\Carbon::now()->local('ja')->isoFormat('YYYY年M月D日（ddd）') }}
            </div>

            <div class="create__item--time">
                {{ \Carbon\Carbon::now()->format('H:i') }}
            </div>      

            <div>お疲れ様でした。（＝退勤済）</div>


        @else
            <!-- 勤務中の時 -->
             <p>出勤中</p>

            <div class="create__item--date">
                {{ \Carbon\Carbon::now()->local('ja')->isoFormat('YYYY年M月D日（ddd）') }}
            </div>

            <div class="create__item--time">
                {{ \Carbon\Carbon::now()->format('H:i') }}
            </div>         
                
            <button type="submit" name="action" value="clock_out">退勤（＝出勤中）</button>
            <button type="submit" name="action" value="break1_start">休憩入（＝出勤中）</button>
        @endif
    </form>
</div>