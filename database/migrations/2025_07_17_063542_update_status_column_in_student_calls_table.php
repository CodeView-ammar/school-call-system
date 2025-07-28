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
        Schema::table('student_calls', function (Blueprint $table) {
            // تعديل الحقل إلى enum أو string بطول مناسب
            $table->string('status', 50)->change(); // أو ->enum([...])->change()
        });
    }

    public function down()
    {
        Schema::table('student_calls', function (Blueprint $table) {
            // العودة إلى النوع السابق (مثلاً integer)
            $table->integer('status')->change(); // أو حسب ما كان سابقًا
        });
    }
};
