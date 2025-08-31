<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateBusesTableChangeDriverId extends Migration
{
    public function up()
    {
        Schema::table('buses', function (Blueprint $table) {
            // حذف قيود المفتاح الخارجي الحالي
            $table->dropForeign(['driver_id']);
            
            // تعديل العمود للإشارة إلى جدول drivers
            $table->unsignedBigInteger('driver_id')->nullable()->change();

            // إضافة قيود المفتاح الخارجي الجديدة
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('buses', function (Blueprint $table) {
            // حذف قيود المفتاح الخارجي
            $table->dropForeign(['driver_id']);
            // استعادة العلاقة مع users
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('set null');
        });
    }
}