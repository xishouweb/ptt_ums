<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBusinessUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_users', function (Blueprint $table) {
            //
            $table->increments('id');
            $table->string('nickname', 100)->nullable();
            $table->string('address', 100);
            $table->string('avatar')->nullable();
            $table->integer('coins')->default(0);
            $table->string('token')->nullable();
            $table->timestamps();
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
        Schema::table('business_users', function (Blueprint $table) {
            //
        });
    }
}
