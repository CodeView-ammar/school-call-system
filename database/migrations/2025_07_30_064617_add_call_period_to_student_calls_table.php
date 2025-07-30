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
            $table->enum('call_period', ['morning', 'evening'])->nullable()->after('call_edate');
        });
    }

    public function down()
    {
        Schema::table('student_calls', function (Blueprint $table) {
            $table->dropColumn('call_period');
        });
    }

};
