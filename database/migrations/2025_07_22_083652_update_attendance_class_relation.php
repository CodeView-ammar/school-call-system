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
    Schema::table('attendances', function (Blueprint $table) {
        $table->dropForeign(['school_class_id']);
        $table->dropColumn('school_class_id');

        $table->foreignId('grade_class_id')->constrained()->onDelete('cascade');
    });
}

public function down()
{
    Schema::table('attendances', function (Blueprint $table) {
        $table->dropForeign(['grade_class_id']);
        $table->dropColumn('grade_class_id');

        $table->foreignId('school_class_id')->constrained()->onDelete('cascade');
    });
}
};
