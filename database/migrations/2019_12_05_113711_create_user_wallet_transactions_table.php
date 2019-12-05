<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserWalletTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_wallet_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('address');
            $table->string('symbol');
            $table->integer('type');
            $table->integer('status')->nullable();
            $table->double('amount')->default(0);
            $table->string('to')->nullable();
            $table->string('from')->nullable();
            $table->double('fee')->nullable();
            $table->string('tx_hash')->nullable();
            $table->integer('block_number')->nullable();
            $table->integer('block_confirm')->nullable();
            $table->string('rate')->nullable();
            $table->string('remark')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('user_id');
            $table->index('address');
            $table->index('symbol');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_wallet_transactions');
    }
}
