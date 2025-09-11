@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/create-attendance.css')}}">
@endsection

@section('content')
<div class="content">
    
<!-- 勤務外・出勤中・休憩中・退勤済　※勤務外と退勤済の違いは？ -->
        <div class="create__item--date">
        {{ \Carbon_Carbon::now()->local('ja')->isoFormat('YYYY年M月D日（ddd）') }}
        </div>

        <div class="create__item--time">
        {{ \Carbon\Carbon::now()->format('H:i') }}
        </div>

        <form action="/attendance" method="POST" >
            <button >出勤（＝勤務外）</button>
            <button>退勤（＝出勤中）</button>
            <button>休憩入（＝出勤中）</button>
            <button>休憩戻（＝休憩中）</button>
            <div>お疲れ様でした。（＝退勤済）</div>

            <!-- attendance-statusのテーブル作成する -->
        </form>

</div>