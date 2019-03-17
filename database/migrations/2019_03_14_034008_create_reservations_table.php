<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->increments('id');
            $table->text('status');
            $table->unsignedInteger('event_id');
            $table->unsignedInteger('attendee_id');
//            $table->unsignedInteger('voucher_id');
            $table->timestamps();
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->foreign('attendee_id')->references('id')->on('attendees')->onDelete('cascade');
//            $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign('reservations_event_id_foreign');
            $table->dropColumn('event_id');
            $table->dropForeign('reservations_attendee_id_foreign');
            $table->dropColumn('attendee_id');
//            $table->dropForeign('reservations_voucher_id_foreign');
//            $table->dropColumn('voucher_id');
        });
        Schema::dropIfExists('reservations');
    }
}
