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
    Schema::table('roles', function (Blueprint $table) {
        $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
    });
}

public function down(): void
{
    Schema::table('roles', function (Blueprint $table) {
        $table->dropForeign(['school_id']);
        $table->dropColumn('school_id');
    });
}
};
