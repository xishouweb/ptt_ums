<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRentRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rent_records', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('team_id', 20);
            $table->decimal('token_amount', 10, 2);
            $table->string('token_type', 50);
            $table->string('action');
            $table->tinyInteger('status')->default(0);
            $table->decimal('settlement_amount', 10, 2)->nullable();
            $table->integer('campaign_id')->nullable();
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
        Schema::dropIfExists('rent_records');
    }
}
