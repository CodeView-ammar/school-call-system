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
        Schema::table('student_calls', function (Blueprint $table) {
            $table->id()->first(); // تضيف العمود id في أول الجدول
        });
    }

    public function down(): void
    {
        Schema::table('student_calls', function (Blueprint $table) {
            $table->dropColumn('id');
        });
    }

};
