<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id(); // هذا ينشئ id تلقائيًا كـ INT AUTO_INCREMENT PRIMARY KEY
            $table->string('name'); // يعادل VARCHAR(255) NOT NULL
            $table->timestamps(); // لإضافة created_at و updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
