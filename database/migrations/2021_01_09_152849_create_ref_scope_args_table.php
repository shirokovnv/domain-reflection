<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRefScopeArgsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ref_scope_args', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ref_scope_id')->nullable();
            $table->string('name');
            $table->boolean('isOptional')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->string('typeHint')->nullable();
            $table->timestamps();

            $table->foreign('ref_scope_id')
                ->references('id')
                ->on('ref_scopes')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ref_scope_args');
    }
}
