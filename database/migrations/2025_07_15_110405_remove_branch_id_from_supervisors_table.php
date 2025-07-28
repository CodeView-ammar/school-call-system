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
        Schema::table('supervisors', function (Blueprint $table) {
            $table->dropForeign(['branch_id']); // في حال كان فيه foreign key
            $table->dropColumn('branch_id');
        });
    }

    public function down()
    {
        Schema::table('supervisors', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
        });
    }

};
