
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
        Schema::table('week_days', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable()->after('day_id');
            $table->unsignedBigInteger('branch_id')->nullable()->after('school_id');
            
            // إضافة المفاتيح الخارجية
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            
            // إضافة فهرس للأداء
            $table->index(['school_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('week_days', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropForeign(['branch_id']);
            $table->dropIndex(['school_id', 'branch_id']);
            $table->dropColumn(['school_id', 'branch_id']);
        });
    }
};
