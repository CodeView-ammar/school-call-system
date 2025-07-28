<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grade_classes', function (Blueprint $table) {
            // حذف العمود القديم
            if (Schema::hasColumn('grade_classes', 'education_level_id')) {
                $table->dropForeign(['education_level_id']);
                $table->dropColumn('education_level_id');
            }

            // إضافة العمود الجديد nullable أولاً
            $table->foreignId('academic_band_id')->nullable()->constrained('academic_bands')->nullOnDelete();
        });

        // بعدها تحتاج تعبئة العمود بالقيم المناسبة يدوياً أو عن طريق سكريبت تحديث بيانات
        // مثال: DB::table('grade_classes')->update(['academic_band_id' => 1]);

        // ثم تعديل العمود ليصبح NOT NULL (يتم تنفيذها في migration منفصل)
    }

    public function down(): void
    {
        Schema::table('grade_classes', function (Blueprint $table) {
            $table->dropForeign(['academic_band_id']);
            $table->dropColumn('academic_band_id');

            $table->foreignId('education_level_id')->constrained('education_levels')->cascadeOnDelete();
        });
    }
};
