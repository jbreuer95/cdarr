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
        Schema::create('encodes', function (Blueprint $table) {
            $table->id();
            $table->string('status');
            $table->integer('progress')->default(0);

            $table->bigInteger('video_file_id')->unsigned();
            $table->foreign('video_file_id')->references('id')->on('video_files')->cascadeOnDelete();

            $table->bigInteger('event_id')->unsigned()->nullable();
            $table->foreign('event_id')->references('id')->on('events')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('encodes');
    }
};
