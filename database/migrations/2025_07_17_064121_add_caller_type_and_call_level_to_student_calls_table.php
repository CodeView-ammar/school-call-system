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
    Schema::table('student_calls', function (Blueprint $table) {
        $table->enum('caller_type', ['guardian', 'assistant', 'bus'])->default('guardian')->after('user_id');
        $table->enum('call_level', ['normal', 'urgent'])->default('normal')->after('caller_type');
    });
}

public function down()
{
    Schema::table('student_calls', function (Blueprint $table) {
        $table->dropColumn(['caller_type', 'call_level']);
    });
}
};
