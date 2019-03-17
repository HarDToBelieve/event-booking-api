<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('event_id');
            $table->double('discount_percent');
            $table->unsignedInteger('start_date');
            $table->unsignedInteger('end_date');
//            $table->text('code');
            $table->string('code', 100);
            $table->timestamps();
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->unique('code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropForeign('vouchers_event_id_foreign');
            $table->dropColumn('event_id');
        });
        Schema::dropIfExists('vouchers');
    }
}
