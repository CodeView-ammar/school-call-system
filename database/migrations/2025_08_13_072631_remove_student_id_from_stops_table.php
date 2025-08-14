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
    Schema::table('stops', function (Blueprint $table) {
            // إذا كان student_id مرتبط بـ foreign key
            $table->dropForeign(['student_id']); // احذف القيود الأجنبية أولاً

            // ثم احذف العمود
            $table->dropColumn('student_id');
        });
    }

    public function down()
    {
        Schema::table('stops', function (Blueprint $table) {
            // إعادة إضافة حقل student_id إذا لزم الأمر
            $table->unsignedBigInteger('student_id')->nullable()->after('id');
        });
    }
};
