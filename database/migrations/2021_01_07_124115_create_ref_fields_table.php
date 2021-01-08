<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRefFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ref_fields', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ref_model_id')->nullable();
            $table->string('name');
            $table->string('label')->nullable();
            $table->string('type')->nullable();
            $table->boolean('fillable')->nullable()->default(false);
            $table->boolean('guarded')->nullable()->default(false);
            $table->boolean('hidden')->nullable()->default(false);
            $table->boolean('required')->nullable()->default(false);
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
        Schema::dropIfExists('ref_fields');
    }
}
