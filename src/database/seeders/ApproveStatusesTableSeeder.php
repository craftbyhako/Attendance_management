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
        DB::table('approve_statuses')->insert([
            [
                'id' => 1,
                'status' => '承認待ち',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'status' => '承認済み',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
