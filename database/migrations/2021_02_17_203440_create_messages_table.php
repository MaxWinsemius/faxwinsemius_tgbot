<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger('bot_user_id');
            $table->string('file',  100)->nullable();
            // Types exist of (2021-17) text, images
            $table->unsignedTinyInteger('type');
            $table->boolean('dispatched')->default(false);
            $table->boolean('printed')->default(false);
            $table->boolean('fileAvailable')->default(false);
            $table->unsignedInteger('stat')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
    }
}
