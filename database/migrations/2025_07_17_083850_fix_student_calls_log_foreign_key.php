<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public function up()
    {
        Schema::table('student_calls_log', function (Blueprint $table) {
            // حذف المفتاح الأجنبي الخاطئ أولاً
            $table->dropForeign(['student_call_id']);
        });

        Schema::table('student_calls_log', function (Blueprint $table) {
            // ثم إعادة إضافته بشكل صحيح مع ربطه بـ call_id
            $table->foreign('student_call_id')
                ->references('call_id')
                ->on('student_calls')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('student_calls_log', function (Blueprint $table) {
            $table->dropForeign(['student_call_id']);
        });

        Schema::table('student_calls_log', function (Blueprint $table) {
            $table->foreign('student_call_id')
                ->references('id') // الرجوع للحالة القديمة (الخاطئة)
                ->on('student_calls')
                ->onDelete('cascade');
        });
    }
};
