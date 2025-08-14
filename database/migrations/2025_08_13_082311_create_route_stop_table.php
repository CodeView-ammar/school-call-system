<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_stop', function (Blueprint $table) {
            $table->unsignedBigInteger('route_id');
            $table->unsignedBigInteger('stop_id');

            $table->primary(['route_id', 'stop_id']);

            $table->foreign('route_id')->references('id')->on('routes')->onDelete('cascade');
            $table->foreign('stop_id')->references('id')->on('stops')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_stop');
    }
};
