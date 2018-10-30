<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaggablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uy_taggables', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->bigInteger('tag_id')->unsigned();
            $table->bigInteger('taggable_id')->unsigned();
            $table->string('taggable_type');

            $table->timestamps();

            //Indices
            $table->unique(['taggable_type', 'taggable_id', 'tag_id']);
            $table->unique(['tag_id', 'taggable_type', 'taggable_id']);

            $table->foreign('tag_id')->references('id')->on('uy_tags')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uy_taggables');
    }
}
