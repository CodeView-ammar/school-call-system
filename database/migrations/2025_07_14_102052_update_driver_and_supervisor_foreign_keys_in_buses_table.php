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
        Schema::table('buses', function (Blueprint $table) {
            // حذف القيود الأجنبية الحالية
            $table->dropForeign(['driver_id']);
            $table->dropForeign(['supervisor_id']);

            // تعديل الحقول لتشير إلى جدول users
            $table->foreign('driver_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('supervisor_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('buses', function (Blueprint $table) {
            // حذف العلاقة الجديدة
            $table->dropForeign(['driver_id']);
            $table->dropForeign(['supervisor_id']);

            // إرجاع العلاقة القديمة (لو كنت تستخدم drivers/supervisors)
            $table->foreign('driver_id')
                ->references('id')
                ->on('drivers')
                ->nullOnDelete();

            $table->foreign('supervisor_id')
                ->references('id')
                ->on('supervisors')
                ->nullOnDelete();
        });
    }

};
