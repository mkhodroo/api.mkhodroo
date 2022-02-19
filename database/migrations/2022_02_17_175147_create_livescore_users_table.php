<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLivescoreUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('livescore_users', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('email', 255)->nullable();
            $table->string('domain', 255);
            $table->string('expire_at', 30);
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
        Schema::dropIfExists('livescore_users');
    }
}
