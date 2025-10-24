<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ApproveStatus;

class UpdatedAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'approve_status_id',
        'update_date',
        'note',
        
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
    
    public function approveStatus()
    {
        return $this->belongsTo(ApproveStatus::class, 'approve_status_id');
    }
}
