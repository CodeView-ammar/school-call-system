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
       Schema::table('grade_classes', function (Blueprint $table) {
            // ✅ إضافة مفتاح مركب على الاسم العربي مع المدرسة
            $table->unique(['school_id', 'name_ar'], 'unique_school_name_ar');

            // ✅ (اختياري) إضافة مفتاح مركب على الاسم الإنجليزي مع المدرسة
            $table->unique(['school_id', 'name_en'], 'unique_school_name_en');
        });
    }

    public function down(): void
    {
        Schema::table('grade_classes', function (Blueprint $table) {
            $table->dropUnique('unique_school_name_ar');
            $table->dropUnique('unique_school_name_en');
        });
    }

};
