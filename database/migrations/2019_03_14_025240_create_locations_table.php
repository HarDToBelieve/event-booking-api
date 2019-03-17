<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->increments('id');
            $table->text('name_location');
//            $table->text('address');
            $table->string('address', 100);
            $table->integer('capacity');
            $table->unsignedInteger('owner_id');
            $table->timestamps();
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->foreign('owner_id')->references('id')->on('organizers')->onDelete('cascade');
            $table->unique('address');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropForeign('locations_owner_id_foreign');
            $table->dropColumn('owner_id');
        });
        Schema::dropIfExists('locations');
    }
}
