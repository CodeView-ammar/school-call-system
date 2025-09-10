<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stops', function (Blueprint $table) {
            $table->foreignId('branch_id')
                ->after('school_id')   // بعد حقل school_id
                ->nullable()           // يمكن تركه فارغ مؤقتًا
                ->constrained('branches') // الربط بالجدول branches
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stops', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};
