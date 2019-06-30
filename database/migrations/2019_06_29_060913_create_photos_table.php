<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('photos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->integer('photo_id');
            $table->string('title');
            $table->string('thumbnailUrl');
            $table->timestamps();
            $table->unique(['user_id','photo_id'], 'user_photo');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // make both colums unique 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('photos');
    }
}
