<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            // إعادة تسمية الأعمدة القديمة
            $table->renameColumn('lic_start_at', 'starts_at');
            $table->renameColumn('lic_end_at', 'ends_at');
            $table->renameColumn('lic_by_user', 'created_by');
            $table->renameColumn('lic_isactive', 'is_active');
            $table->renameColumn('lic_cust_code', 'school_id'); // أو يمكنك وضع علاقة FK لاحقًا

            // تعديل نوع البيانات إذا لزم الأمر
            $table->boolean('is_active')->default(true)->change();
            $table->dateTime('starts_at')->change();
            $table->dateTime('ends_at')->change();
        });
    }

    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->renameColumn('starts_at', 'lic_start_at');
            $table->renameColumn('ends_at', 'lic_end_at');
            $table->renameColumn('created_by', 'lic_by_user');
            $table->renameColumn('is_active', 'lic_isactive');
            $table->renameColumn('school_id', 'lic_cust_code');

            $table->string('lic_isactive')->change();
            $table->string('lic_start_at')->change();
            $table->string('lic_end_at')->change();
        });
    }
};
