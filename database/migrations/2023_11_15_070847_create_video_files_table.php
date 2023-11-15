<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('video_files', function (Blueprint $table) {
            $table->id();
            $table->text('path');

            $table->integer('duration')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->string('codec')->nullable();
            $table->string('profile')->nullable();
            $table->string('level')->nullable();
            $table->string('color_space')->nullable();
            $table->string('frame_rate')->nullable();
            $table->string('bit_rate')->nullable();

            $table->bigInteger('movie_id')->unsigned()->nullable();
            $table->foreign('movie_id')->references('id')->on('movies')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_files');
    }
};
