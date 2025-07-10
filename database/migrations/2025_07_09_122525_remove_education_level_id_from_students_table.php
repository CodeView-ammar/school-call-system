<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class RemoveEducationLevelIdFromStudentsTable extends Migration
{
        public function up(): void
        {
            Schema::table('students', function (Blueprint $table) {
                // أولًا نحذف القيد (إن كان معرفًا باسم افتراضي أو صريح)
                $table->dropForeign(['education_level_id']); // <== تأكد أن هذا اسم العمود الصحيح

                // ثم نحذف العمود نفسه
                $table->dropColumn('education_level_id');
            });
        }

        public function down(): void
        {
            Schema::table('students', function (Blueprint $table) {
                $table->unsignedBigInteger('education_level_id')->nullable();

                $table->foreign('education_level_id')
                    ->references('id')
                    ->on('education_levels')
                    ->onDelete('set null');
            });
        }
    
}
