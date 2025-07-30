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
     Schema::table('branches', function (Blueprint $table) {
            // تأكد من عدم وجود تكرار قبل تفعيل القيود
            $table->unique(['school_id', 'code'], 'unique_school_code');
            $table->unique(['school_id', 'name_ar'], 'unique_school_name_ar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropUnique('unique_school_code');
            $table->dropUnique('unique_school_name_ar');
        });
    }
};
