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
        Schema::create('call_types', function (Blueprint $table) {
            $table->id('id');
            $table->string('ctype_name_eng')->nullable()->comment('اسم نوع النداء بالإنجليزية');
            $table->string('ctype_name_ar')->comment('اسم نوع النداء بالعربية');
            $table->string('ctype_cdate')->nullable()->comment('تاريخ الإنشاء');
            $table->string('ctype_udate')->nullable()->comment('تاريخ التحديث');
            $table->integer('ctype_isactive')->nullable()->default(1)->comment('حالة النشاط');
            $table->string('ctype_cust_code')->nullable()->comment('كود العميل');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_types');
    }
};