<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('guardian_supervisor', function (Blueprint $table) {
        $table->id();
        $table->foreignId('guardian_id')->constrained()->onDelete('cascade');
        $table->foreignId('supervisor_id')->constrained()->onDelete('cascade');
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guardian_supervisor');
    }
};
