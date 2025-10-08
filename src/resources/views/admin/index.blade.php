@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/index.css')}}">
@endsection

@section('content')
<div class="container">
    <h1 class="page-title">{{ $target_day_display }}の勤怠一覧</h1>
    <div class="index__table"></div>
        <div class="index__group--target">
            <!-- 前日リンク -->
            <a href="{{ route('user.index', ['day' => $prev_day]) }}"><img src="{{ asset('storage/img/left_arrow.png') }}" alt="">　前日</a>
        
            <!-- 当日（デフォルト） -->
            <div class="center-day">
                <img src="{{ asset('storage/img/calender.png') }}">
                {{ $target_day }}
            </div>

            <!-- 次日リンク -->
            <a href="{{ route('user.index', ['day' => $next_day]) }}">翌日　<img src="{{ asset('storage/img/right_arrow.png') }}" alt=""></a>
        </div>

        <div class="index__group--table">
            <table>
                <thead>
                    <tr>
                        <th>名前</th>
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
                        <td>{{ $attendance->user->user_name ?? '' }}</td>
                        <td>{{ $attendance->clock_in}}</td>
                        <td>{{ $attendance->clock_out}}</td>
                        <td>{{ $attendance->totalBreakTime }}</td>
                        <td>{{ $attendance->totalWorkingTime }}</td>
                        <td><a href="{{ route('user.showDetail', ['id' => $attendance->id]) }}">詳細</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

