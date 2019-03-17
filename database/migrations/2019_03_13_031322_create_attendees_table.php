<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttendeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendees', function (Blueprint $table) {
            $table->increments('id');
            $table->text('firstname')->nullable();
            $table->text('lastname')->nullable();
//            $table->text('email');
            $table->string('email', 50);
            $table->text('phone')->nullable();
            $table->text('password')->nullable();
            $table->text('signup_code')->nullable();
            $table->timestamps();
        });

        Schema::table('attendees', function (Blueprint $table) {
            $table->unique('email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendees');
    }
}
