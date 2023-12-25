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

            $table->boolean('analysed')->default(false);
            $table->boolean('encoded')->default(false);

            $table->integer('index')->nullable();
            $table->integer('nb_streams')->nullable();
            $table->integer('duration')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->string('container_format')->nullable();
            $table->string('codec')->nullable();
            $table->string('codec_id')->nullable();
            $table->string('profile')->nullable();
            $table->string('level')->nullable();
            $table->string('video_range')->nullable();
            $table->string('pixel_format')->nullable();
            $table->string('color_range')->nullable();
            $table->string('color_space')->nullable();
            $table->string('color_transfer')->nullable();
            $table->string('color_primaries')->nullable();
            $table->string('chroma_location')->nullable();
            $table->integer('bit_depth')->nullable();
            $table->string('frame_rate')->nullable();
            $table->integer('bit_rate')->nullable();
            $table->boolean('interlaced')->default(false);
            $table->boolean('anamorphic')->default(false);
            $table->boolean('faststart')->default(false);

            $table->bigInteger('movie_id')->unsigned()->nullable();
            $table->foreign('movie_id')->references('id')->on('movies')->nullOnDelete();

            $table->bigInteger('episode_id')->unsigned()->nullable();
            $table->foreign('episode_id')->references('id')->on('episodes')->nullOnDelete();

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
