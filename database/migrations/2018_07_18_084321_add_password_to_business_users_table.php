<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPasswordToBusinessUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('business_users', function (Blueprint $table) {
            $table->string('password')->unique();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business_users', function (Blueprint $table) {
            $table->dropUnique('business_users_password_unique');
            $table->dropColumn('password');
        });
    }
}
