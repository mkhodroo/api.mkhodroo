<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAuthorityToLivescoreUserCredit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('livescore_user_credit', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->string('authority', 255)->after('credit');
            $table->enum('status', ['ok', 'pending', 'cancel'])->after('refId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('livescore_user_credit', function (Blueprint $table) {
            //
        });
    }
}
