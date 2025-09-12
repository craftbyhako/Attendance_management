@extends('lyaouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/detail.css')}}">
@endsection

@section('content')
<div class="content">
    <h1 class="page-title">勤怠詳細</h1>

    <form action="/attendance/detail/{id}" method="POST">
        @csrf
<!-- 名前と日付はinputじゃないかも？それかinputだけど、cssでボーダーを消す？？hidden？？ -->
        <label class="detail-form__label" for="user_name">名前</label>
        <input class="detail-form__input" type="text" id="user_name" value="{{ $user }}">

        <label class="detail-form__label" for="date">日付</label>
        <input class="detail-form__input" type="date" id="date" value="{{ $data }}">

        <!-- divがいいのか、pがいいのか？ -->
        <div class="detail-form__label" >出勤・退勤
            <input class="detail-form__label" type="time" value="{{ $clock_in }}">
            <p>～</p>
            <input class="detail-form__label" type="time" value="{{ $clock_out}}">
        </div>

        <label class="detail-form__label" for="break1">休憩</label>
        <input class="detail-form__input" type="time" id="break1" value="{{ $break1_start }}">
        <p>～</p>
        <input class="detail-form__input" type="time" id="break1" value="{{ $break1_end }}">

        <label class="detail-form__label" for="break2">休憩２</label>
        <input class="detail-form__input" type="time" id="break2" value="{{ $break2_start }}">
        <p>～</p>
        <input class="detail-form__input" type="time" id="break2" velue="{{ $break2_end }}">

        <label class="detail-form__label" for="note">備考</label>
        <input class="detail-foem__input" type="text" id="note" value="{{ $note }}">

        <button class="detail-form__button" type="submit">修正</button>
    </form>

    @if
    <!-- 申請済なら、①承認待ちのため修正はできませんの赤字表示、②inputできなくなる -->

    @endif
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