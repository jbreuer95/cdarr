<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranscodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transcodes', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->enum('service', ['sonarr', 'radarr']);
            $table->enum('status', [
                'waiting',
                'starting',
                'uploading',
                'transcoding',
                'downloading',
                'failed',
                'finished'
            ])->default('waiting');
            $table->integer('progress')->default(0);
            $table->string('cmd')->nullable();
            $table->text('webhook_data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transcodes');
    }
}
