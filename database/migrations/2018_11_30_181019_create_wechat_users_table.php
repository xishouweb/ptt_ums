<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWechatUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wechat_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('openid')->unique()->nullable();
            $table->string('app_openid')->unique()->nullable();
            $table->string('unionid')->unique()->nullable();
            $table->string('nickname')->nullable();
            $table->string('headimgurl')->nullable();
            $table->string('sex')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('province')->nullable();
            $table->string('language')->nullable();
            $table->integer('subscribe')->nullable();
            $table->string('subscribe_time')->nullable();
            $table->string('remark')->nullable();
            $table->string('groupid')->nullable();
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
        Schema::dropIfExists('wechat_users');
    }
}
