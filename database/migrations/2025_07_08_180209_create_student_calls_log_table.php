<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_calls_log', function (Blueprint $table) {
            $table->id();

            // العلاقة مع student_calls
            $table->foreignId('student_call_id')
                ->constrained('student_calls', 'call_id')
                ->onDelete('cascade');

            // الحالة الجديدة
            $table->unsignedTinyInteger('status');

            // وقت تغيير الحالة
            $table->timestamp('changed_at')->default(now());

            // المستخدم المسؤول عن التغيير
            $table->foreignId('changed_by_user_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->timestamps(); // optional for created_at and updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_calls_log');
    }
};
