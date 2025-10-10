<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\UpdatedAttendance;
use App\Models\ApproveStatus;

class ApprovalController extends Controller
{
    public function indexUpdated(Request $request) {
    
        // クエリパラメータ ?page=pending or ?page=updated
        $page = $request->query('page', 'pending'); // デフォルトは承認待ち

        $query = \App\Models\UpdatedAttendance::with(['attendance', 'approveStatus', 'user'])
        ->orderBy('updated_at', 'desc');

        // ステータスでフィルタ
        if ($page === 'pending') {
        $query->whereHas('approveStatus', function ($q) {
            $q->where('status', '承認待ち');
        });
        } elseif ($page === 'updated') {
        $query->whereHas('approveStatus', function ($q) {
            $q->where('status', '承認済み');
        });
        }

        $requests = $query->get();

        return view('admin.request-list', compact('requests', 'page'));
    }
    
    public function showRequest($id) {
        
        $attendance = Attendance::find($id);

        // 承認ボタン押下時にロック解除
        $attendance->is_editable = true;
        $attendance->save();

        $isLocked = $attendance->is_editable === true;
        
        $targetDate = Carbon::parse($attendance->year_month. '-'. $attendance->day)
        ->locale('ja')
        ->isoFormat('YYYY年M月D日');

        $clock_in = $attendance->clock_in ? trim(Carbon::parse($attendance->clock_in)->format('H:i')) : '';
        $clock_out = $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '';
        $break1_start = $attendance->break1_start ? trim(Carbon::parse($attendance->break1_start)->format('H:i')) : '';
        $break1_end = $attendance->break1_end ? trim(Carbon::parse($attendance->break1_end)->format('H:i')) : '';
        $break2_start = $attendance->break2_start ? trim(Carbon::parse($attendance->break2_start)->format('H:i')) : '';
        $break2_end = $attendance->break2_end ? trim(Carbon::parse($attendance->break2_end)->format('H:i')) : '';
              
        return view('user.detail', compact('attendance','user','targetDate', 'clock_in', 'clock_out','break1_start', 'break1_end', 'break2_start', 'break2_end', 'isLocked'));
    }

    
    
}