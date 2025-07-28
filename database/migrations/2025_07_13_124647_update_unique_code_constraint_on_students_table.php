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
            // حذف القيد الفريد القديم على code (إن وُجد)
            $table->dropUnique(['code']); // إذا لم يكن موجوداً، تجاهل هذا السطر لاحقاً

            // إضافة قيد فريد مركب
            $table->unique(['school_id', 'code'], 'unique_code_per_school');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique('unique_code_per_school');

            // إعادة قيد فريد على code لو أردت
            $table->unique('code');
        });
    }
};
