@extends('layouts.admin-app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/user-list.css')}}">
@endsection

@section('content')
<div class="container">
    <h1 class="page-title">スタッフ一覧</h1>
    <div class="user-list__table"></div>

        <div class="user-list__group--table">
            <table>
                <thead>
                    <tr>
                        <th>名前</th>
                        <th>メールアドレス</th>
                        <th>月次勤怠</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->user_name ?? '' }}</td>
                        <td>{{ $user->email ?? '' }}</td>
                        <td><a href="{{ route('admin.userAttendances', ['user' => $user->user_name]) }}">詳細</a></tb>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

