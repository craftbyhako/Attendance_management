@extends('lyaouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/detail.css')}}">
@endsection

@section('content')
<div class="content">
    <h1 class="page-title">勤怠詳細</h1>

    <form action="">
        @csrf

        <label for="">名前</label>
        <input type="text">

        <label for="">日付</label>
        <input type="text">

        <label for="">出勤・退勤</label>
        <input type="text">

        <label for="">休憩</label>
        <input type="text">

        <label for="">休憩２</label>
        <input type="text">

        <label for="">備考</label>
        <input type="text">

        <button >修正</button>
    </form>

    <!-- <table>
        <tr>
            <th>名前</th>
            <td>{{ $user_name }}</td>
        </tr>

        <tr>
            <th>日付</th>
            <td>{{ $target_date }}</td>
        </tr>

        <tr>
            <th>出勤・退勤</th>
            <td>{{ $clock_in }} ～  {{ $clock_out }}</td>
        </tr>

        <tr>
            <th>休憩</th>
            <td>{{ $break1_start }} ～  {{ $break1_end }}</td>
        </tr>

        <tr>
            <th>休憩２</th>
            <td>{{ $break2_start }} ～  {{ $break2_end }}</td>
        </tr>

        <tr>
            <th>備考</th>
            <td>{{ $note }}</td>
        </tr>


    </table> -->



</div>


@endsection