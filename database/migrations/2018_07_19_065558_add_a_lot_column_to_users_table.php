<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddALotColumnToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('address', 100);
            $table->string('address_password')->nullable();
            $table->string('phone')->unique();
            $table->string('update_key')->unique();
            $table->string('nickname', 100)->nullable();
            $table->string('avatar')->nullable();
            $table->integer('coins')->default(0);
            $table->string('token')->nullable();
            $table->string('type', 15)->default('data_user');
            $table->softDeletes();
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
            $table->dropUnique('users_phone_unique');
            $table->dropUnique('users_update_key_unique');
            $table->dropColumn('address');
            $table->dropColumn('address_password');
            $table->dropColumn('phone');
            $table->dropColumn('nickname');
            $table->dropColumn('avatar');
            $table->dropColumn('coins');
            $table->dropColumn('token');
            $table->dropColumn('type');
            $table->dropSoftDeletes();
        });
    }
}
