<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRefFkeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ref_fkeys', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ref_field_id')->nullable();
            $table->string('name');
            $table->string('foreign_table');
            $table->string('references');
            $table->timestamps();

            $table->foreign('ref_field_id')->references('id')->on('ref_fields')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ref_fkeys');
    }
}
