<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePendingTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uy_pending_tags', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->string('name', 30);
            $table->unsignedInteger('hit')->default(0);

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
        Schema::dropIfExists('uy_pending_tags');
    }
}
