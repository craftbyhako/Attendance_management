<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\UpdatedAttendance;


class UpdatedAttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'user_id' => 1,
            'attendance_id' => 1,
            'approve_status_id' => 0,
            'update_date' => '2025-09-10',
            'note' => '所用のため早退します。',
        ];
        DB::table('updated_attendances')->insert($param);
    
        $param = [
            'user_id' => 2,
            'attendance_id' => 2,
            'approve_status_id' => 1,
            'update_date' => '2025-09-10',
            'note' => '電車の遅延のため遅刻。',
        ];
        DB::table('updated_attendances')->insert($param);
    
    }
}
