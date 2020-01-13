<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserActionHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_action_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('type');
            $table->integer('user_id');
            $table->integer('transaction_id')->nullable();
            $table->integer('saving_id')->nullable();
            $table->integer('withdrawal_id')->nullable();
            $table->double('balance')->nullable();
            $table->string('remark')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('type');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_action_histories');
    }
}
