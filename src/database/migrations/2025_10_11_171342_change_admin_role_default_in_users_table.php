<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeAdminRoleDefaultInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // NULLのデータは0に更新
        DB::table('users')->whereNull('admin_role')->update(['admin_role' => 0]);

        // DoctrineなしでSQL直接実行（MySQL想定）
        DB::statement("ALTER TABLE users MODIFY admin_role INT DEFAULT 0");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       DB::statement("ALTER TABLE users MODIFY admin_role INT DEFAULT NULL");
    }
}
