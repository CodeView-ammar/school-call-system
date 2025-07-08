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
        Schema::create('academic_band_week_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('academic_band_id')->constrained('academic_bands')->onDelete('cascade');
            $table->foreignId('week_day_id')->constrained('week_days', 'day_id')->onDelete('cascade');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            // فهارس لتحسين الأداء
            $table->index(['school_id', 'academic_band_id']);
            $table->index(['school_id', 'week_day_id']);
            
            // منع التكرار
            $table->unique(['school_id', 'academic_band_id', 'week_day_id'], 'unique_school_band_day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_band_week_days');
    }
};
