<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('week_days', function (Blueprint $table) {
            $table->id('day_id');
            $table->string('day')->comment('اليوم');
            $table->time('time_to')->comment('الوقت إلى');
            $table->time('time_from')->comment('الوقت من');
            $table->string('day_inactive')->nullable()->comment('حالة عدم النشاط');
            $table->string('branch_code')->nullable()->comment('كود الفرع');
            $table->string('customer_code')->nullable()->comment('كود العميل');
            $table->integer('band_id')->comment('معرف الباند');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('week_days');
    }
};