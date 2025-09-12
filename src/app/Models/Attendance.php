<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_statuses_id',
        'year_month',
        'day',
        'clock_in',
        'clock_out',
        'break1_start',
        'break1_end',
        'break2_start',
        'break2_end',
    ];
}
