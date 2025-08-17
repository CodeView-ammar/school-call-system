<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('routes')->onDelete('cascade');
            $table->date('effective_date');
            $table->integer('repeated_every_days')->default(1); // كم يوم تتكرر الرحلة
            $table->time('arrival_time_at_first_stop');
            $table->integer('stop_to_stop_time_minutes')->default(5); // الوقت بين المحطات بالدقائق
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->onDelete('set null');
            $table->foreignId('bus_id')->nullable()->constrained('buses')->onDelete('set null');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // فهارس لتحسين الأداء
            $table->index(['route_id', 'effective_date']);
            $table->index(['school_id', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('trips');
    }
};