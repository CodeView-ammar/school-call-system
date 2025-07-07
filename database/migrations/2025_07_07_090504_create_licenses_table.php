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
        Schema::create('licenses', function (Blueprint $table) {
            $table->id('lic_id');
            $table->string('lic_start_at')->comment('تاريخ بداية الترخيص');
            $table->string('lic_end_at')->comment('تاريخ انتهاء الترخيص');
            $table->integer('lic_by_user')->comment('معرف المستخدم');
            $table->string('lic_cdate')->nullable()->comment('تاريخ الإنشاء');
            $table->string('lic_udate')->nullable()->comment('تاريخ التحديث');
            $table->string('lic_cust_code')->nullable()->comment('كود العميل');
            $table->string('lic_isactive')->nullable()->default('1')->comment('حالة النشاط');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};