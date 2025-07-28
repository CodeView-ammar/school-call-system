<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grade_classes', function (Blueprint $table) {
            $table->foreignId('academic_band_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('grade_classes', function (Blueprint $table) {
            $table->foreignId('academic_band_id')->nullable()->change();
        });
    }
};
