<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyHolidaysTable extends Migration
{
    public function up()
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->string('name_en')->nullable()->change(); // اجعل الحقل قابلًا لأن يكون فارغًا
        });
    }

    public function down()
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->string('name_en')->nullable(false)->change(); // استرجع الحالة الأصلية
        });
    }
}