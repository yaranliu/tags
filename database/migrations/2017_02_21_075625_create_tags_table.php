<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uy_tags', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->string('name', 30);

            $table->timestamps();

            //Indices
            $table->unique(['name']);
            $table->index('created_at');
            $table->index('updated_at');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uy_tags');
    }
}
