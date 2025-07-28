<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSchoolAndBranchToEducationLevelsTable extends Migration
{
    public function up(): void
    {
        Schema::table('education_levels', function (Blueprint $table) {
            // ✅ إضافة school_id إجباري (NOT NULL)
            // $table->unsignedBigInteger('school_id');

            // ❌ إزالة branch_id (لو تم إضافته سابقًا)
            if (Schema::hasColumn('education_levels', 'branch_id')) {
                $table->dropColumn('branch_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('education_levels', function (Blueprint $table) {
            // في حال الرجوع، نحذف school_id ونعيد branch_id
            // $table->dropColumn('school_id');
            $table->unsignedBigInteger('branch_id')->nullable();
        });
    }
}
