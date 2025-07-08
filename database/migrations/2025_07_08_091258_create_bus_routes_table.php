<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bus_routes')) {
            Schema::create('bus_routes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
                $table->foreignId('bus_id')->constrained('buses')->cascadeOnDelete();
                $table->string('name_ar')->comment('اسم المسار بالعربية');
                $table->string('name_en')->nullable()->comment('اسم المسار بالإنجليزية');
                $table->string('code')->unique()->comment('كود المسار');

                // نقاط البداية
                $table->decimal('route_road_from_lat', 10, 8)->comment('خط العرض لنقطة البداية');
                $table->decimal('route_road_from_lng', 11, 8)->comment('خط الطول لنقطة البداية');
                $table->text('route_road_from_address')->nullable()->comment('عنوان نقطة البداية');

                // نقاط النهاية
                $table->decimal('route_road_to_lat', 10, 8)->comment('خط العرض لنقطة النهاية');
                $table->decimal('route_road_to_lng', 11, 8)->comment('خط الطول لنقطة النهاية');
                $table->text('route_road_to_address')->nullable()->comment('عنوان نقطة النهاية');

                // الرحلات
                $table->boolean('route_is_go')->default(true)->comment('مؤشر رحلة الذهاب');
                $table->boolean('route_is_return')->default(false)->comment('مؤشر رحلة العودة');

                // معلومات إضافية
                $table->integer('estimated_time')->nullable()->comment('الوقت المقدر بالدقائق');
                $table->decimal('distance_km', 8, 2)->nullable()->comment('المسافة بالكيلومتر');
                $table->text('description')->nullable()->comment('وصف المسار');
                $table->text('notes')->nullable()->comment('ملاحظات');

                $table->boolean('is_active')->default(true);
                $table->timestamps();

                // فهارس
                $table->index(['school_id', 'is_active']);
                $table->index(['bus_id', 'is_active']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bus_routes');
    }
};
