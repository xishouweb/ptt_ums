<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTokenAmountAndOriginalAmountAndAfterAmountToTokenTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('token_transactions', function (Blueprint $table) {
            $table->decimal('token_amount', 16, 4)->change();
            $table->decimal('original_amount', 16, 4)->change();
            $table->decimal('after_amount', 16, 4)->change();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('token_transactions', function (Blueprint $table) {
            $table->decimal('token_amount', 10, 2)->change();
            $table->decimal('original_amount', 10, 2)->change();
            $table->decimal('after_amount', 10, 2)->change();
        });
    }
}
