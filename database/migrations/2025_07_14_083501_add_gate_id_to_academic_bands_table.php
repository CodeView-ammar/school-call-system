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
        Schema::table('academic_bands', function (Blueprint $table) {
            $table->foreignId('gate_id')->nullable()->after('id')->constrained()->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('academic_bands', function (Blueprint $table) {
            $table->dropForeign(['gate_id']);
            $table->dropColumn('gate_id');
        });
    }
};
