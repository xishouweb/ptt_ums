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
            $table->string('address')->nullable();
            $table->string('symbol')->nullable();
            $table->integer('type')->nullable();
            $table->integer('status')->nullable();
            $table->double('amount')->default(0);
            $table->string('to')->nullable();
            $table->string('from')->nullable();
            $table->double('fee')->nullable();
            $table->string('tx_hash')->nullable();
            $table->integer('block_number')->nullable();

            $table->string('payload', 2000)->nullable();

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
        Schema::dropIfExists('transaction_action_histories');
    }
}
