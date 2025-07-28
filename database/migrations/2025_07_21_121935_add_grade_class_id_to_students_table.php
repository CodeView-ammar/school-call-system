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
        Schema::table('students', function (Blueprint $table) {
            // إزالة المفتاح الأجنبي أولاً (إن وجد)
            $table->dropForeign(['school_class_id']);
            // ثم إزالة العمود
            $table->dropColumn('school_class_id');

            // إضافة العمود الجديد وربطه بجدول grade_classes
            $table->unsignedBigInteger('grade_class_id')->nullable()->after('branch_id');
            $table->foreign('grade_class_id')->references('id')->on('grade_classes')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // عكس العملية: إعادة الحقول القديمة
            $table->dropForeign(['grade_class_id']);
            $table->dropColumn('grade_class_id');

            $table->unsignedBigInteger('school_class_id')->nullable();
            $table->foreign('school_class_id')->references('id')->on('grade_classes')->onDelete('set null');
        });
    }
};
