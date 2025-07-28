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
        Schema::table('student_calls', function (Blueprint $table) {
            // أولًا نحذف المفتاح الأجنبي إن وُجد
            if (Schema::hasColumn('student_calls', 'call_type_id')) {
                $table->dropForeign(['call_type_id']); // حذف المفتاح الأجنبي
                $table->dropColumn('call_type_id');    // حذف العمود
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_calls', function (Blueprint $table) {
            $table->unsignedBigInteger('call_type_id')->nullable();

            // إذا كنت تريد استرجاع المفتاح الأجنبي (اختياري)
            $table->foreign('call_type_id')->references('id')->on('call_types')->nullOnDelete();
        });
    }
};
