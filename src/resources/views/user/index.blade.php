@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/index.css')}}">
@endsection

@section('content')
<div class="container">
    <h1 class="page-title">勤怠一覧</h1>
    <div class="index__group--target">
        <a href="{{ route('user.index', ['month => $prev_month']) }}">⇐　前月</a>
        
        <image src="">
        {{ $target_month }}

        <a href="{{ route('user.index', ['month => $prev_month']) }}">⇒　翌月</a>
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
                    
                    {{-- <td>{{ $attendance->day }}</td>
                    <td>{{ $attendance->clock_in}}</td>
                    <td>{{ $attendance->clock_out}}</td>
                    <td>{{ $break_timetarget_break_total}}</td>
                    <td>{{ $target_work_total}}</td>
                    <td><a href="/attendance/detail/{id}">詳細</a></td>  --}}
                </tr>
                @endforeach
            </tbody>
        </table>
    </form>
</div>
@endsection

