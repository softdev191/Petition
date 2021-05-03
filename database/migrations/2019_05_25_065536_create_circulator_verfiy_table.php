<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCirculatorVerfiyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('circulator_verify', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('signerpetition_id');
            $table->bigInteger('circulatorpetition_id');
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
        Schema::dropIfExists('circulator_verify');
    }
}
