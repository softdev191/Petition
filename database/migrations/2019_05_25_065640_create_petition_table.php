<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePetitionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('petition', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('subject');
            $table->mediumText('description');
            $table->mediumText('terms');
            $table->string('video_url');
            $table->bigInteger('user_id');
            $table->integer('state_id');
            $table->string('image_url')->nullable();
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
        Schema::dropIfExists('petition');
    }
}
