<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTokenTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('token_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('blockchain_tx_hash')->nullable();
            $table->decimal('token_amount', 10, 2);
            $table->string('token_type', 50);
            $table->string('action', 50);
            $table->decimal('original_amount', 10, 2);
            $table->decimal('after_amount', 10, 2);
            $table->tinyInteger('status')->default(0);
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
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
        Schema::dropIfExists('token_transactions');
    }
}
