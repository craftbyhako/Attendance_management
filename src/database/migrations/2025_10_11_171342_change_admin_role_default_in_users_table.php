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
        DB::table('users')->whereNull('admin_role')->update(['admin_role' => 0]);


        Schema::table('users', function (Blueprint $table) {

            $table->integer('admin_role')->default(0)->change();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('admin_role')->nullable()->default(null)->change();
        });
    }
}
