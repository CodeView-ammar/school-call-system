<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('grade_classes', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable(false)->change();
        });
    }

    public function down()
    {
        Schema::table('grade_classes', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->change();
        });
    }
};
