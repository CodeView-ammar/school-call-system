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
    Schema::table('students', function (Blueprint $table) {
        $table->unique(['school_id', 'code']);
    });
}

public function down(): void
{
    Schema::table('students', function (Blueprint $table) {
        $table->dropUnique(['school_id', 'code']);
    });
}

};
