<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMixersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mixers', function (Blueprint $table) {
            $table->id();
            $table->string('from_wallet_address');
            $table->string('from_wallet_private_key');
            $table->string('from_txid');
            $table->integer('value');
            $table->string('to_wallet_address');
            $table->integer('level')->default(3);
            $table->integer('deep')->default(4);
            $table->integer('start_wallet_id');
            $table->integer('end_wallet_id');
            $table->boolean('is_test');
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
        Schema::dropIfExists('mixers');
    }
}
