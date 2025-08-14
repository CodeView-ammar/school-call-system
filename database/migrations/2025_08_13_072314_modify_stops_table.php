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
    Schema::table('stops', function (Blueprint $table) {
                // إزالة حقل student_id إذا كان موجودًا
                // $table->dropColumn('student_id');

                // إضافة حقل school_id
                $table->foreignId('school_id')->constrained()->after('id'); // تأكد من إضافة هذا السطر
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
          Schema::table('stops', function (Blueprint $table) {
            // إعادة إضافة حقل student_id
            $table->unsignedBigInteger('student_id')->nullable()->after('id');

            // إزالة حقل school_id
            $table->dropForeign(['school_id']);
            $table->dropColumn('school_id');
        });
    }
};
