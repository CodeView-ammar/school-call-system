<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('education_levels', function (Blueprint $table) {
            // قاعدة فريدة: نفس المدرسة + الاسم العربي
            $table->unique(['school_id', 'name_ar'], 'education_levels_school_name_ar_unique');

            // قاعدة فريدة: نفس المدرسة + الاسم المختصر
            $table->unique(['school_id', 'short_name'], 'education_levels_school_short_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('education_levels', function (Blueprint $table) {
            $table->dropUnique('education_levels_school_name_ar_unique');
            $table->dropUnique('education_levels_school_short_name_unique');
        });
    }
};
