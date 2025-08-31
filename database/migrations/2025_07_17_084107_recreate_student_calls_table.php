<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // إزالة الـ foreign key من جدول log مؤقتًا
        Schema::table('student_calls_log', function (Blueprint $table) {
            $table->dropForeign(['student_call_id']);
        });

        // حذف الجدول القديم student_calls
        Schema::dropIfExists('student_calls');

        // إنشاء الجدول الجديد student_calls
        Schema::create('student_calls', function (Blueprint $table) {
            $table->id(); // primary key auto increment
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->dateTime('call_cdate')->nullable();
            $table->dateTime('call_edate')->nullable();
            $table->string('status')->default('0');
            $table->string('caller_type')->default('guardian');
            $table->string('call_level')->default('normal');
            $table->timestamps();
        });

        // إعادة إضافة الـ foreign key لجدول log
        Schema::table('student_calls_log', function (Blueprint $table) {
            $table->foreign('student_call_id')->references('id')->on('student_calls')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // حذف foreign key من log قبل حذف الجدول
        Schema::table('student_calls_log', function (Blueprint $table) {
            $table->dropForeign(['student_call_id']);
        });

        Schema::dropIfExists('student_calls');
    }
};
