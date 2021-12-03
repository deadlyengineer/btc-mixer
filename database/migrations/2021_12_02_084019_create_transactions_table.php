<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('total_value');
            $table->integer('fees');
            $table->string('input_address');
            $table->integer('input_outpoint_total_value');
            $table->string('input_outpoint_txid');
            $table->string('output1_address');
            $table->string('output1_value');
            $table->string('output2_address')->nullable();
            $table->string('output2_value')->nullable();
            $table->integer('sent_index');
            $table->integer('layer_deep');
            $table->integer('nodeId');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
