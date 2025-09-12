@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/updated-attendance.css')}}">
@endsection

@section('content')
<div class="content">
    <h1 class="page-title">申請一覧</h1>
    <div class="tab">
        <ul class="tab__list">
            <li><a href="{{ route(approval.index), 'tab' => 'request') }}">申請一覧</a></li>
            <li><a href="{{ route(approval.index), 'tab' => 'approve' }}">承認一覧</a></li>
        </ul>
    </div>

    <div class="requests">
        <div class="request">
            <table>
                <thead>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @foreach( $requests as $request)
                        <td>{{ $attendance_status }}</td>
                        <td>{{ $user_name }}</td>
                        <td>{{ $target_date }}</td>
                        <td>{{ $note }}</td>
                        <td>{{ $update_date }}</td>
                        <td><a href="">詳細</a></td>
                    </tr>
                </tbody>
                
            

            </div>
        </table>
    </div>
    
</div>