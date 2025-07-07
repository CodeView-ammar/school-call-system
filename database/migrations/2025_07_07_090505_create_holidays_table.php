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
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('holiday_name_ar')->comment('اسم العطلة بالعربية');
            $table->string('holiday_name_en')->nullable()->comment('اسم العطلة بالإنجليزية');
            $table->string('holiday_from_date')->comment('تاريخ بداية العطلة');
            $table->string('holiday_to_date')->comment('تاريخ نهاية العطلة');
            $table->string('holiday_isactive')->nullable()->default('1')->comment('حالة النشاط');
            $table->string('holiday_cust_code')->nullable()->comment('كود العميل');
            $table->string('holiday_cdate')->nullable()->comment('تاريخ الإنشاء');
            $table->string('holiday_udate')->nullable()->comment('تاريخ التحديث');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};