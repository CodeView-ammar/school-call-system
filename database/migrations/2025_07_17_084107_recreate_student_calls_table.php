<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RecreateStudentCallsTable extends Migration
{
    public function up()
    {
        // إعادة تسمية الجدول القديم مؤقتًا
        Schema::rename('student_calls', 'old_student_calls');

        // إنشاء جدول جديد student_calls مع عمود id كـ primary key
        Schema::create('student_calls', function (Blueprint $table) {
            $table->id(); // هذا هو id autoincrement
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('school_id');
            $table->dateTime('call_cdate')->nullable();
            $table->dateTime('call_edate')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('status');
            $table->unsignedBigInteger('branch_id');
            $table->string('caller_type')->default('guardian');
            $table->string('call_level')->default('normal');
            $table->timestamps();

            // علاقات
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
        });

        // نقل البيانات من الجدول القديم للجديد
        DB::table('student_calls')->insertUsing([
            'call_id as id', 'student_id', 'school_id', 'call_cdate', 'call_edate',
            'user_id', 'status', 'branch_id', 'created_at', 'updated_at',
            'caller_type', 'call_level'
        ], DB::table('old_student_calls'));

        // حذف الجدول القديم
        Schema::drop('old_student_calls');
    }

    public function down()
    {
        Schema::dropIfExists('student_calls');
    }
}
