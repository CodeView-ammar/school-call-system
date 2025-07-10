
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // تحديث القيم الموجودة أولاً
        DB::table('week_days')
            ->where('day_inactive', '1')
            ->orWhere('day_inactive', 'true')
            ->orWhere('day_inactive', 'TRUE')
            ->update(['day_inactive' => true]);
            
        DB::table('week_days')
            ->where('day_inactive', '0')
            ->orWhere('day_inactive', 'false')
            ->orWhere('day_inactive', 'FALSE')
            ->orWhereNull('day_inactive')
            ->update(['day_inactive' => false]);

        // تعديل نوع العمود
        Schema::table('week_days', function (Blueprint $table) {
            $table->boolean('day_inactive')->default(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('week_days', function (Blueprint $table) {
            $table->string('day_inactive')->nullable()->change();
        });
    }
};
