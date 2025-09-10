<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            // إضافة عمود branch_id
            $table->foreignId('branch_id')
                ->after('school_id') // اختياري لتحديد المكان
                ->nullable() // إذا تريد السماح بأن يكون فارغ مؤقتاً
                ->constrained('branches') // ربطه بجدول branches
                ->cascadeOnDelete();   // عند حذف الفرع، تحذف المسارات التابعة له
        });
    }

    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->dropForeign(['branch_id']); // حذف المفتاح الخارجي
            $table->dropColumn('branch_id');   // حذف العمود
        });
    }
};
