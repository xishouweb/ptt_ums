<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSavingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('savings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('approver_id')->nullable();
            $table->integer('type');
            $table->integer('status');
            $table->string('title');
            $table->string('title_en')->nullable();
            $table->string('icon');
            $table->double('rate');
            $table->integer('yield_time');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->text('detail_rule');
            $table->text('detail_rule_en')->nullable();
            $table->double('entry_standard');
            $table->timestamps();
            $table->softDeletes();
            $table->index('user_id');
            $table->index('approver_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('savings');
    }
}
