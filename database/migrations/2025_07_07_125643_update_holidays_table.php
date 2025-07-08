<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     public function up()
    {
        Schema::table('holidays', function (Blueprint $table) {
            // تحقق مما إذا كان العمود موجودًا قبل حذفه
            if (Schema::hasColumn('holidays', 'holiday_cust_code')) {
                $table->dropColumn('holiday_cust_code');
            }

            // إضافة الحقول الجديدة
            $table->string('name_ar')->after('id'); // الاسم العربي
            $table->string('name_en')->after('name_ar'); // الاسم الإنجليزي
            $table->date('from_date')->after('name_en'); // تاريخ البداية
            $table->date('to_date')->after('from_date'); // تاريخ النهاية
            $table->boolean('is_active')->default(true)->after('to_date'); // حالة النشاط

            // إضافة علاقة مع المدرسة
            $table->foreignId('school_id')->constrained()->after('is_active'); // إضافة المدرسة
        });
    }

    public function down()
    {
        Schema::table('holidays', function (Blueprint $table) {
            // إعادة الحقول المحذوفة
            $table->string('holiday_cust_code')->nullable()->after('holiday_isactive');

            // إزالة الحقول الجديدة
            $table->dropForeign(['school_id']);
            $table->dropColumn(['name_ar', 'name_en', 'from_date', 'to_date', 'is_active', 'school_id']);
        });
    }
};
