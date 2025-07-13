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
        Schema::table('guardian_supervisor', function (Blueprint $table) {
            $table->dateTime('assigned_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('guardian_supervisor', function (Blueprint $table) {
            $table->dropColumn('assigned_date');
        });
    }

};
