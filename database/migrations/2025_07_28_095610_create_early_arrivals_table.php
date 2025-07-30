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
        Schema::create('early_arrivals', function (Blueprint $table) {
          $table->id();

        // العلاقات
        $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
        $table->foreignId('guardian_id')->nullable()->constrained('guardians')->onDelete('cascade');
        $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
        $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
        $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // الموظف الذي سجل الحضور

        // الحقول الأساسية
        $table->date('pickup_date');
        $table->time('pickup_time');
        $table->string('pickup_reason')->nullable(); // سبب الاستلام المبكر
        $table->enum('status', ['pending', 'approved', 'rejected', 'completed', 'canceled'])->default('pending'); // حالة الطلب

        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('early_arrivals');
    }
};
