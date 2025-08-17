<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('trip_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('trips')->onDelete('cascade');
            $table->foreignId('stop_id')->constrained('stops')->onDelete('cascade');
            $table->time('arrival_time');
            $table->integer('stop_order')->default(1); // ترتيب المحطة في الرحلة
            $table->boolean('is_pickup')->default(true); // هل هذه محطة صعود؟
            $table->boolean('is_dropoff')->default(true); // هل هذه محطة نزول؟
            $table->timestamps();

            // فهارس لتحسين الأداء
            $table->index(['trip_id', 'stop_order']);
            $table->unique(['trip_id', 'stop_id']); // منع تكرار المحطة في نفس الرحلة
        });
    }

    public function down()
    {
        Schema::dropIfExists('trip_stops');
    }
};