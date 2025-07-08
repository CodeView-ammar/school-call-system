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
        Schema::table('students', function (Blueprint $table) {
            // إضافة الأعمدة المطلوبة
            $table->string('student_number')->unique()->after('id');
            $table->string('name_ar')->after('name');
            $table->string('name_en')->nullable()->after('name_ar');
            $table->date('date_of_birth')->nullable()->after('name_en');
            $table->enum('gender', ['male', 'female'])->after('date_of_birth');
            $table->string('nationality')->nullable()->after('gender');
            $table->string('national_id')->nullable()->after('nationality');
            $table->text('medical_notes')->nullable()->after('photo');
            $table->string('emergency_contact')->nullable()->after('medical_notes');
            $table->integer('bus_id')->nullable()->after('emergency_contact');
            $table->text('pickup_location')->nullable()->after('bus_id');
            $table->text('address_en')->nullable()->after('address');
            
            // إعادة تسمية أعمدة موجودة
            $table->renameColumn('address', 'address_ar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'student_number', 'name_ar', 'name_en', 'date_of_birth', 
                'gender', 'nationality', 'national_id', 'medical_notes',
                'emergency_contact', 'bus_id', 'pickup_location', 'address_en'
            ]);
            $table->renameColumn('address_ar', 'address');
        });
    }
};
