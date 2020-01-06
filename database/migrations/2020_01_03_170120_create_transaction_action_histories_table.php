<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionActionHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_action_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable();
            $table->string('symbol', 10)->nullable();
            $table->string('type', 10)->nullable();
            $table->integer('status')->default(0);
            $table->double('amount')->default(0);
            $table->string('to', 100)->nullable();
            $table->string('from', 100)->nullable();
            $table->double('fee')->nullable();
            $table->string('tx_hash', 100)->nullable();
            $table->integer('block_number')->nullable();

            $table->string('payload', 2000)->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->index('user_id');
            $table->index('to');
            $table->index('from');
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
        Schema::dropIfExists('transaction_action_histories');
    }
}
