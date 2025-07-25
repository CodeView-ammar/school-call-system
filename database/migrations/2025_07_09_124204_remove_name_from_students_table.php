<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveNameFromStudentsTable extends Migration
{
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('name')->nullable(false);
        });
    }
}
