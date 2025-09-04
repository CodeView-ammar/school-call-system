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
            // تغيير نوع العمود إلى VARCHAR
            $table->string('status')->change();
        });
    }

    public function down()
    {
        Schema::table('student_calls_log', function (Blueprint $table) {
            // إعادة تغيير نوع العمود إلى TINYINT
            $table->tinyInteger('status')->unsigned()->change();
        });
    }
};
