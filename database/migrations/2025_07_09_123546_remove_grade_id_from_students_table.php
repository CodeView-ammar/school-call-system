<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
        public function up(): void
        {
            Schema::table('students', function (Blueprint $table) {
                // أولًا نحذف القيد (إن كان معرفًا باسم افتراضي أو صريح)
                $table->dropForeign(['grade_id']); // <== تأكد أن هذا اسم العمود الصحيح

                // ثم نحذف العمود نفسه
                $table->dropColumn('grade_id');
            });
        }

        public function down(): void
        {
            Schema::table('students', function (Blueprint $table) {
                $table->unsignedBigInteger('grade_id')->nullable();

                $table->foreign('grade_id')
                    ->references('id')
                    ->on('grade')
                    ->onDelete('set null');
            });
        }
   

};
