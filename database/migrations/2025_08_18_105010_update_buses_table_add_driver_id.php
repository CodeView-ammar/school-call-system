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
        Schema::table('buses', function (Blueprint $table) {
            // تحقق إذا كان الحقل موجودًا بالفعل
            if (!Schema::hasColumn('buses', 'driver_id')) {
                $table->unsignedBigInteger('driver_id')->nullable()->after('branch_id');

                // إضافة قيود المفتاح الخارجي
                $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('buses', function (Blueprint $table) {
            // حذف قيود المفتاح الخارجي
            $table->dropForeign(['driver_id']);
            $table->dropColumn('driver_id');
        });
    }
};