<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
        // إضافة subscription_id إذا لم يكن موجوداً
        if (!Schema::hasColumn('licenses', 'subscription_id')) {
            $table->foreignId('subscription_id')
                ->nullable()
                ->constrained('subscriptions')
                ->onDelete('cascade')
                ->after('school_id');
        }

        // تحديث نوع is_active فقط
        $table->boolean('is_active')->default(true)->change();
    });

    }

    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            // عكس التعديلات إذا لزم
            $table->renameColumn('id', 'lic_id');
            if (Schema::hasColumn('licenses', 'subscription_id')) {
                $table->dropForeign(['subscription_id']);
                $table->dropColumn('subscription_id');
            }
            if (Schema::hasColumn('licenses', 'created_at')) {
                $table->renameColumn('created_at', 'lic_cdate');
            }
            if (Schema::hasColumn('licenses', 'updated_at')) {
                $table->renameColumn('updated_at', 'lic_udate');
            }
        });
    }
};
