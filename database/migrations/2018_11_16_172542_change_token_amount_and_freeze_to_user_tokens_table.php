<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTokenAmountAndFreezeToUserTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_tokens', function (Blueprint $table) {
            $table->decimal('token_amount', 16, 4)->change();
            $table->decimal('freeze', 16, 4)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_tokens', function (Blueprint $table) {
            $table->decimal('token_amount', 10, 2)->change();
            $table->decimal('freeze', 10, 2)->change();
        });
    }
}
