<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBotUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bot_users', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->bigInteger('userid')->unique();
            $table->boolean('printAccess')->default(false);
            $table->string('firstName')->nullable();
            $table->string('lastName')->nullable();
            $table->dateTime('lastPrintRequest')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bot_users');
    }
}
