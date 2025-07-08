<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveDuplicateColumnsFromHolidaysTable extends Migration
{
   public function up()
    {
        Schema::table('holidays', function (Blueprint $table) {
            // إزالة الأعمدة المكررة
            $table->dropColumn(['holiday_name_ar', 'holiday_name_en', 'holiday_from_date', 'holiday_to_date', 'holiday_isactive']);
        });
    }

    public function down()
    {
        // Schema::table('holidays', function (Blueprint $table) {
        //     // إعادة الأعمدة إذا تم التراجع عن الهجرة
        //     $table->string('holiday_name_ar')->after('id')->nullable(false);
        //     $table->string('holiday_name_en')->after('holiday_name_ar')->nullable();
        //     $table->date('holiday_from_date')->after('holiday_name_en')->nullable(false);
        //     $table->date('holiday_to_date')->after('holiday_from_date')->nullable(false);
        //     $table->boolean('holiday_isactive')->default(true)->after('holiday_to_date');
        // });
    }}