<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRefRelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ref_relations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ref_model_id')->nullable();
            $table->string('name');
            $table->string('type')->nullable();
            $table->text('keys')->nullable();
            $table->timestamps();

            $table->foreign('ref_model_id')->references('id')->on('ref_models')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ref_relations');
    }
}
