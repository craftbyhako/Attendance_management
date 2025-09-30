@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/index.css')}}">
@endsection

@section('content')
<div class="container">
    <h1 class="page-title">勤怠一覧</h1>
    <div class="index__table"></div>
        <div class="index__group--target">
            <!-- 前月リンク -->
            <a href="{{ route('user.index', ['month' => $prev_month]) }}"><img src="{{ asset('img/left_arrow.png') }}" alt="">前月</a>
        
            <!-- 当月（デフォルト） -->
            <div class="center-month">
                <img src="{{ asset('storage/img/calender.png') }}">
                {{ $target_month }}
            </div>

            <!-- 次月リンク -->
            <a href="{{ route('user.index', ['month' => $prev_month]) }}"><img src="storage/img/right_arrow.png" alt="">翌月</a>
        </div>

        <div class="index__group--table">
            <table>
                <thead>
                    <tr>
                        <th>日付</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $attendance)
                    <tr>
                    
                        <td>{{ Carbon\Carbon::parse($attendance->year_month . '-' . $attendance->day)->locale('ja')->isoFormat('MM/DD (ddd) ') }}</td>
                        <td>{{ $attendance->clock_in}}</td>
                        <td>{{ $attendance->clock_out}}</td>
                        <td>{{ $attendance->totalBreakTime }}</td>
                        <td>{{ $attendance->totalWorkingTime }}</td>
                        <td><a href="{{ route('user.detail', ['id' => $attendance->id]) }}">詳細</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

