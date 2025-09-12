<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ApproveStatus; 
use Illuminate\Support\Facades\DB;



class ApproveStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses = [
            '承認待ち',
            '承認済み',
        ];

        foreach($statuses as $status) {
            DB::table('approve_statuses')->insert([
                'status' => $status,
            ]);
        }
    }
}
