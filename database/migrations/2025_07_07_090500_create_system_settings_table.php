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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id('sys_id');
            $table->string('sys_earlyexit')->nullable()->comment('إعداد الخروج المبكر');
            $table->string('sys_earlycall')->nullable()->comment('إعداد المكالمة المبكرة');
            $table->string('sys_return_call')->nullable()->comment('إعداد مكالمة العودة');
            $table->string('sys_exit_togat')->nullable()->comment('إعداد خروج التوجات');
            $table->string('sys_cust_code')->nullable()->comment('كود العميل');
            $table->string('sys_cdate')->nullable()->comment('تاريخ الإنشاء');
            $table->string('sys_udate')->nullable()->comment('تاريخ التحديث');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};