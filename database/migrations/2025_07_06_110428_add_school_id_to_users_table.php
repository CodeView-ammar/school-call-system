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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('user_type')->default('staff'); // 'super_admin', 'school_admin', 'staff'
            $table->boolean('can_manage_school')->default(false);
            $table->json('school_permissions')->nullable(); // صلاحيات خاصة بالمدرسة
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropColumn(['school_id', 'user_type', 'can_manage_school', 'school_permissions']);
        });
    }
};
