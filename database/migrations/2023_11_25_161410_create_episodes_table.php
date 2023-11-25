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
        Schema::create('episodes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('sonarr_episode_id')->unsigned()->unique();
            $table->bigInteger('sonarr_file_id')->unsigned()->unique();

            $table->integer('season')->nullable();
            $table->integer('episode')->nullable();
            $table->string('quality')->nullable();

            $table->bigInteger('serie_id')->unsigned();
            $table->foreign('serie_id')->references('id')->on('series')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};
