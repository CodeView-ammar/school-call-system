<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('student_calls', function (Blueprint $table) {
            $table->id('call_id'); // معرف النداء

            // نوع النداء (علاقة مع جدول call_types)
            $table->foreignId('call_type_id')->constrained('call_types')->onDelete('cascade');

            // كود الطالب (علاقة مع جدول students حسب id)
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');

            // كود المدرسة (علاقة مع جدول schools حسب id)
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');

            // تاريخ النداء
            $table->dateTime('call_cdate')->nullable();
            $table->dateTime('call_edate')->nullable();

            // المستخدم الذي أضاف النداء (علاقة مع users)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // الحالة (مثلاً 0 = غير منفذ، 1 = منفذ...)
            $table->unsignedTinyInteger('status')->default(0);

            // كود الفرع (علاقة مع جدول branches حسب id)
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_calls');
    }

};