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
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('radarr_movie_id')->unsigned()->unique();
            $table->bigInteger('radarr_file_id')->unsigned()->unique();

            $table->string('title');
            $table->integer('year')->nullable();
            $table->string('studio')->nullable();
            $table->string('quality')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
