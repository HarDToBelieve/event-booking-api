<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->increments('id');
//            $table->text('title');
            $table->string('title', 100);
            $table->text('description');
            $table->unsignedInteger('start_date');
            $table->unsignedInteger('end_date');
            $table->unsignedInteger('location_id');
            $table->unsignedInteger('owner_id');
            $table->text('category');
            $table->text('img')->nullable();
            $table->text('type');
            $table->unsignedInteger('capacity');
            $table->timestamps();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->foreign('owner_id')->references('id')->on('organizers')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unique('title');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign('events_owner_id_foreign');
            $table->dropColumn('owner_id');
            $table->dropForeign('events_location_id_foreign');
            $table->dropColumn('location_id');
        });
        Schema::dropIfExists('events');
    }
}
