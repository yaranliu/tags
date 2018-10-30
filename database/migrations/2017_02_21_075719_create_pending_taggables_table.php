<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePendingTaggablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uy_pending_taggables', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->bigInteger('pending_tag_id')->unsigned();
            $table->bigInteger('taggable_id')->unsigned();
            $table->string('taggable_type');

            $table->timestamps();

            //Indices
            $table->unique(['taggable_type', 'taggable_id', 'pending_tag_id'], 'type_id_tag_id');
            $table->unique(['pending_tag_id', 'taggable_type', 'taggable_id'], 'tag_id_type_id');

            $table->foreign('pending_tag_id')->references('id')->on('uy_pending_tags')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uy_pending_taggables');
    }
}
