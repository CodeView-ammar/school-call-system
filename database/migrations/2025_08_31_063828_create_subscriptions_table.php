<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_id')->constrained('subscription_types')->onDelete('cascade');
            $table->decimal('price', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('max_students')->nullable();
            $table->integer('max_calls')->nullable();
            $table->integer('max_buses')->nullable();
            $table->integer('max_classes')->nullable();
            $table->integer('max_users')->nullable();
            $table->integer('max_drivers')->nullable();
            $table->integer('max_branches')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
