<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWechatOpenidsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wechat_openids', function (Blueprint $table) {
            $table->increments('id');
            $table->string('openid');
            $table->string('unionid');
            $table->string('channel', 20);
            $table->integer('subscribe')->default(0);
            $table->integer('user_id')->nullable();
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
        Schema::dropIfExists('wechat_openids');
    }
}
