<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApproveStatus extends Model
{
    use HasFactory;

    protected $table = 'approve_statuses'; 

    protected $fillable = [
        'status',
    ];

    public function updatedAttendance()
    {
        return $this->hasMany(UpdatedAttendance::class);
    }
}
