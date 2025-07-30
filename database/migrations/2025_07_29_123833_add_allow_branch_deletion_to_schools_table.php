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
            $table->boolean('allow_branch_deletion')->default(true)->after('max_branches'); // ضع بعد العمود المناسب
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
          Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn('allow_branch_deletion');
        });
    }
};
