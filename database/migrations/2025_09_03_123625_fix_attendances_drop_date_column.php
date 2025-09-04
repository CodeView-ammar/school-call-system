<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ أولاً ننقل القيم من date إلى attendance_date (لو فاضي)
        DB::table('attendances')->whereNull('attendance_date')->update([
            'attendance_date' => DB::raw('`date`')
        ]);

        // ✅ بعدها نحذف العمود date
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('date');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->date('date')->nullable(); // لو رجعت rollback
        });
    }
};
