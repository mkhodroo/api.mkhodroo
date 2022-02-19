<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLivescoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('livescores', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('host',200);
            $table->string('host_goals', 10);
            $table->string('guest_goals',10);
            $table->string('guest',200);
            $table->string('match_time',100);
            $table->string('match_timer',50);
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
        Schema::dropIfExists('livescores');
    }
}
