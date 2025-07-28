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
    Schema::table('grade_classes', function (Blueprint $table) {
        $table->foreignId('branch_id')->constrained()->after('school_id');
    });
}

public function down()
{
    Schema::table('grade_classes', function (Blueprint $table) {
        $table->dropForeign(['branch_id']);
        $table->dropColumn('branch_id');
    });
}
};
