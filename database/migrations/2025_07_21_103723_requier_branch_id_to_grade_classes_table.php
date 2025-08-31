<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('grade_classes', function (Blueprint $table) {
            // حذف المفتاح الأجنبي القديم إذا كان موجودًا
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $foreignKeys = $sm->listTableForeignKeys('grade_classes');

            if (isset($foreignKeys['grade_classes_branch_id_foreign'])) {
                $table->dropForeign('grade_classes_branch_id_foreign');
            }

            // إنشاء المفتاح الأجنبي
            $table->foreign('branch_id')
                  ->references('id')
                  ->on('branches')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('grade_classes', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
        });
    }
};
