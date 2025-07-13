<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToSupervisorsTable extends Migration
{
    public function up()
    {
        Schema::table('supervisors', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('branch_id');
            $table->string('employee_id')->nullable()->after('user_id');
            $table->string('position')->nullable()->after('email');
            $table->decimal('salary', 10, 2)->nullable()->after('position');
            // تأكد من إضافة المفاتيح الأجنبية إذا لزم الأمر، مثلا:
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('supervisors', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'employee_id', 'position', 'salary']);
        });
    }
}
