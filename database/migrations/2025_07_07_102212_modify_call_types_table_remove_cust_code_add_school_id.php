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
        Schema::table('call_types', function (Blueprint $table) {
            // حذف العمود ctype_cust_code
            $table->dropColumn('ctype_cust_code');

            // إضافة عمود school_id مع مفتاح خارجي إلى جدول المدارس (افترض اسمه 'schools' وعمود المفتاح 'id')
            $table->unsignedBigInteger('school_id')->nullable()->after('ctype_isactive');

            $table->foreign('school_id')
                ->references('id')
                ->on('schools')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('call_types', function (Blueprint $table) {
            // إعادة إضافة العمود القديم
            $table->string('ctype_cust_code')->nullable()->after('ctype_isactive');

            // إزالة المفتاح الخارجي والعمود school_id
            $table->dropForeign(['school_id']);
            $table->dropColumn('school_id');
        });
    }
};

