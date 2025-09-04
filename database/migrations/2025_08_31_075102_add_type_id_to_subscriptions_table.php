<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('type_id')      // اسم العمود الجديد
                  ->after('id')               // مكان العمود
                  ->nullable()                // يمكن أن يكون فارغاً
                  ->constrained('subscription_types') // يربطه بجدول SubscriptionType
                  ->onDelete('cascade');      // عند حذف النوع يحذف الاشتراك تلقائياً
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['type_id']); // حذف العلاقة
            $table->dropColumn('type_id');    // حذف العمود
        });
    }
};
