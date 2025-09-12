<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; 
use App\Models\User;


class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'user_name' => 'test1',
            'email' => 'test@example.com1',
            'password' => Hash::make('password'),
            'admin_role' => 0,
        ];
        DB::table('users')->insert($param);

        $param = [
            'user_name' => 'adm1',
            'email' => 'test@example.com2',
            'password' => Hash::make('password'),
            'admin_role' => 1,
        ];
        DB::table('users')->insert($param);
        ]


    }
}
