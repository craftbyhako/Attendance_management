<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\UpdatedAttendance;
use App\Models\ApproveStatus;
use Illuminate\Support\Facades\DB;


class ApprovalController extends Controller
{
    public function indexRequests(Request $request) {
    
        // クエリパラメータ ?page=pending or ?page=updated
        $page = $request->query('page', 'pending'); // デフォルトは承認待ち

        $latestIds = DB::table('updated_attendances')
            ->selectRaw('MAX(id) as id')
            ->groupBy('attendance_id');

        $query = UpdatedAttendance::with(['attendance', 'approveStatus', 'user'])
            ->whereIn('id', $latestIds->pluck('id')->toArray())
            ->orderBy('updated_at', 'desc');

        // ステータスでフィルタ
        if ($page === 'pending') {
            $query->where('approve_status_id', 1);
        } elseif ($page === 'updated') {
            $query->where('approve_status_id', 2);
        }

        $requests = $query->get();

        return view('admin.request-list', compact('requests', 'page'));
    }
    
    public function showRequest($id) {

        // $attendance = Attendance::with(['user', 'updatedAttendances'])->findOrFail($id);

        // $updatedAttendance = $attendance->updatedAttendances()->latest('created_at')->first();
        
        // $canApprove = $updatedAttendance && $updatedAttendance->approve_status_id == 1;
        // $isLocked =  !$canApprove;

        // $targetDate = Carbon::parse($attendance->year_month. '-'. $attendance->day)
        // ->locale('ja')
        // ->isoFormat('YYYY年M月D日');

        $updatedAttendance = UpdatedAttendance::with('attendance.user')->findOrFail($id);
        $attendance = $updatedAttendance->attendance;

        $canApprove = $updatedAttendance->approve_status_id == 1;
        $isLocked = !$canApprove;

        $targetDate = Carbon::parse($attendance->year_month. '-'. $attendance->day)
            ->locale('ja')
            ->isoFormat('YYYY年M月D日');






        $clock_in = $attendance->clock_in ? trim(Carbon::parse($attendance->clock_in)->format('H:i')) : '';
        $clock_out = $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '';
        $break1_start = $attendance->break1_start ? trim(Carbon::parse($attendance->break1_start)->format('H:i')) : '';
        $break1_end = $attendance->break1_end ? trim(Carbon::parse($attendance->break1_end)->format('H:i')) : '';
        $break2_start = $attendance->break2_start ? trim(Carbon::parse($attendance->break2_start)->format('H:i')) : '';
        $break2_end = $attendance->break2_end ? trim(Carbon::parse($attendance->break2_end)->format('H:i')) : '';
              
        $note = $updatedAttendance->note ?? $attendance->note ?? '';

        return view('admin.stamp', compact('attendance', 'updatedAttendance','note','targetDate', 'clock_in', 'clock_out','break1_start', 'break1_end', 'break2_start', 'break2_end', 'isLocked', 'canApprove'));
    }

    public function updateRequest(Request $request, $id) {
        
        
        // $attendance = Attendance::with('updatedAttendances')->findOrFail($id);

        // $updated = $attendance->updatedAttendances()
        //     ->latest('update_date')
        //     ->first(); 

        // // 承認ボタン押下時にロック解除
        // if ($updated && $updated->approve_status_id == 1) {
        //     $updated->approve_status_id = 2;
        //     $updated->save();
        // }

        $updated = UpdatedAttendance::findOrFail($id);

        // 承認ボタン押下時にロック解除
        if ($updated->approve_status_id == 1) {
            $updated->approve_status_id = 2;
            $updated->save();
        }


        // return redirect()-> route('approval.showRequest', ['id' => $attendance->id]);

        return redirect()->route('approval.showRequest', ['id' => $updated->id]);

    }

    
}