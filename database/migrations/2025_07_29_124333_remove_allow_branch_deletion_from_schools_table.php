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
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn('allow_branch_deletion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
           Schema::table('schools', function (Blueprint $table) {
            // هنا ترجع العمود لو حبيت ترجعه (مثلاً Boolean أو حسب نوعه الأصلي)
            $table->boolean('allow_branch_deletion')->default(false);
        });
    }
};
