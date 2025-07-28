<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
Schema::table('education_levels', function (Blueprint $table) {
    $table->unsignedBigInteger('school_id')->nullable();
    $table->unsignedBigInteger('branch_id')->nullable();
});
    }

    public function down(): void
    {
        Schema::table('education_levels', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropColumn('school_id');

            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};
