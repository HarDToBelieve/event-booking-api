<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganizersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organizers', function (Blueprint $table) {
            $table->increments('id');
            $table->text('firstname');
            $table->text('lastname');
//            $table->text('email');
            $table->string('email', 50);
            $table->text('phone');
            $table->text('password');
            $table->timestamps();
        });

        Schema::table('organizers', function (Blueprint $table) {
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
        Schema::dropIfExists('organizers');
    }
}
