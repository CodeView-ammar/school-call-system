<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueConstraintToAcademicBandWeekDaysTable extends Migration
{
    /**
     * تشغيل الهجرة.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('academic_band_week_days', function (Blueprint $table) {
            // إضافة قيد التفرد على الحقول school_id, academic_band_id, و week_day_id
            $table->unique(['school_id', 'academic_band_id', 'week_day_id'], 'unique_school_band_week_day');
        });
    }

    /**
     * التراجع عن الهجرة.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('academic_band_week_days', function (Blueprint $table) {
            // حذف قيد التفرد عند التراجع عن الهجرة
            $table->dropUnique('unique_school_band_week_day');
        });
    }
}
