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
            $table->dropForeign(['call_type_id']);
            $table->unsignedBigInteger('call_type_id')->nullable()->change();
            $table->foreign('call_type_id')->references('id')->on('call_types')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('student_calls', function (Blueprint $table) {
            $table->dropForeign(['call_type_id']);
            $table->unsignedBigInteger('call_type_id')->nullable(false)->change();
            $table->foreign('call_type_id')->references('id')->on('call_types');
        });
    }
};
