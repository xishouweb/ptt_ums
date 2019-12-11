<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserWalletWithdrawalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_wallet_withdrawals', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('approver_id')->nullable();
            $table->string('symbol');
            $table->integer('status');
            $table->double('amount')->default(0);
            $table->string('to');
            $table->string('from')->nullable();
            $table->double('fee');
            $table->string('remark')->nullable();
            $table->text('device_info')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('user_id');
            $table->index('symbol');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_wallet_withdrawals');
    }
}
