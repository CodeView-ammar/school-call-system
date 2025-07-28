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
        Schema::table('week_days', function (Blueprint $table) {
            // إزالة الأعمدة
            $table->dropColumn(['customer_code', 'band_id']);
        });
    }

    public function down()
    {
        Schema::table('week_days', function (Blueprint $table) {
            // إعادة إضافة الأعمدة في حالة التراجع
            $table->string('customer_code')->nullable();
            $table->unsignedBigInteger('band_id')->nullable();
        });
    }
};
