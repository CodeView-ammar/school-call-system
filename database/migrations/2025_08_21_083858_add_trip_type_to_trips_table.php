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
        Schema::table('trips', function (Blueprint $table) {
            $table->enum('trip_type', ['morning', 'evening'])->default('morning'); // إضافة عمود نوع الرحلة
        });
    }

    public function down()
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn('trip_type'); // حذف العمود عند التراجع
        });
    }
};
