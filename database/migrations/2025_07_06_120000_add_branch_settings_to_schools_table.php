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
            $table->integer('max_branches')->default(1);
            $table->integer('current_branches_count')->default(0);
            $table->boolean('allow_unlimited_branches')->default(false);
            $table->json('branch_settings')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn([
                'max_branches',
                'current_branches_count', 
                'allow_unlimited_branches',
                'branch_settings'
            ]);
        });
    }
};