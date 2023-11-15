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
        Schema::create('audio_streams', function (Blueprint $table) {
            $table->id();

            $table->string('codec')->nullable();
            $table->string('lang')->nullable();
            $table->string('channels')->nullable();
            $table->string('sample_rate')->nullable();
            $table->string('bit_rate')->nullable();

            $table->bigInteger('video_file_id')->unsigned();
            $table->foreign('video_file_id')->references('id')->on('video_files')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audio_streams');
    }
};
