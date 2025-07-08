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
    Schema::create('academic_bands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('education_level_id')->constrained()->onDelete('cascade');
            $table->string('name_ar'); // الاسم العربي
            $table->string('name_en'); // الاسم الإنجليزي
            $table->string('short_name'); // الاسم المختصر
            $table->boolean('is_active')->default(true); // حالة النشاط
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_bands');
    }
};
