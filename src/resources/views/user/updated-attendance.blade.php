@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/updated-attendance.css')}}">
@endsection

@section('content')
<div class="content">
    <h1 class="page-title">申請一覧</h1>
    <div class="tab">
        <ul class="tab__list">
            <li><a href="/stamp_correction_request/list?page=pending">承認待ち</a></li>
            <li><a href="/stamp_correction_request/list?page=updated">承認済み</a></li>
        </ul>
    </div>

    <div class="tab__content">
        <div class="tab__index">
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
                    @foreach( $requests as $request)
                        @if ($request->approve_status_id === 1)
                        <tr class="tab__index--pending">
                            <td>{{ $request->approveStatus->status ?? '' }}</td>
                            <td>{{ $request->user->user_name ?? '' }}</td>
                            <td>{{ $request->attendance->year_month ?? '' }}-{{ $request->attendance->day ?? '' }}</td>
                            <td>{{ $request->attendance->note ?? '' }}</td>
                            <td>{{ $request->created_at->format('Y-m-d') }}</td>
                            <td><a href="{{ route('user.showDetail', ['id' => $request->attendance_id]) }}">詳細</a></td>
                        </tr>
                    @else
                        <tr class="tab__index--updated">
                            <td>{{ $request->approveStatus->status ?? '' }}</td>
                            <td>{{ $request->user->user_name ?? '' }}</td>
                            <td>{{ $request->attendance->year_month ?? '' }}-{{ $request->attendance->day ?? '' }}</td>
                            <td>{{ $request->attendance->note ?? '' }}</td>
                            <td>{{ $request->created_at->format('Y-m-d') }}</td>
                            <td><a href="{{ route('user.showDetail', ['id' => $request->attendance_id]) }}">詳細</a></td>
                        </tr>   
                        @endif           
                    @endforeach
                </tbody>
            </table>
        </div>    
</div>
@endsection