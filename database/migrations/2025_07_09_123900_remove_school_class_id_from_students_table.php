<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up()
{
    Schema::table('students', function (Blueprint $table) {
        $table->dropForeign(['school_class_id']); // حذف المفتاح الأجنبي إن وجد
        $table->dropColumn('school_class_id');    // حذف العمود نفسه
    });
}

public function down()
{
    Schema::table('students', function (Blueprint $table) {
        $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
    });
}

};
