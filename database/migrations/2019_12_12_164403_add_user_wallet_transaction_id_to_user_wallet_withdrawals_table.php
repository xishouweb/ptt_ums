<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserWalletTransactionIdToUserWalletWithdrawalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_wallet_withdrawals', function (Blueprint $table) {
            $table->integer('user_wallet_transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_wallet_withdrawals', function (Blueprint $table) {
            $table->dropColumn('user_wallet_transaction_id');
        });
    }
}
