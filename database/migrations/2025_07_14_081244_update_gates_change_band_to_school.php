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
        Schema::table('gates', function (Blueprint $table) {
            $table->foreignId('school_id')->after('name')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gates', function (Blueprint $table) {
              $table->dropForeign(['school_id']);
        $table->dropColumn('school_id');
        });
    }
};
