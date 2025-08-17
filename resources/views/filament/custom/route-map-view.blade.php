
<div class="space-y-4">
    <!-- معلومات المسار -->
    <div class="bg-white dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $route->name }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">المدرسة: {{ $route->school->name_ar }}</p>
            </div>
            <div class="flex items-center space-x-2 rtl:space-x-reverse">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                    {{ $route->stops->count() }} محطة
                </span>
            </div>
        </div>
    </div>

    <!-- عرض المسار كنود متصلة -->
    <div class="bg-white dark:bg-gray-900 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 text-center">مسار الرحلة</h4>
        
        @if($route->stops->count() > 0)
            <div class="relative">
                <!-- الخط الرئيسي للمسار -->
                <div class="absolute right-8 top-12 bottom-12 w-1 bg-gradient-to-b from-green-500 via-blue-500 to-red-500 rounded-full shadow-sm"></div>
                
                <div class="space-y-6">
                    @foreach($route->stops as $index => $stop)
                        <div class="relative flex items-center">
                            <!-- نود المحطة -->
                            <div class="relative z-10 flex-shrink-0">
                                @if($index === 0)
                                    <!-- محطة البداية -->
                                    <div class="w-16 h-16 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center shadow-lg border-4 border-white dark:border-gray-800">
                                        <x-heroicon-s-play class="w-6 h-6 text-white mr-1" />
                                    </div>
                                @elseif($index === $route->stops->count() - 1)
                                    <!-- محطة النهاية -->
                                    <div class="w-16 h-16 bg-gradient-to-br from-red-400 to-red-600 rounded-full flex items-center justify-center shadow-lg border-4 border-white dark:border-gray-800">
                                        <x-heroicon-s-stop class="w-6 h-6 text-white" />
                                    </div>
                                @else
                                    <!-- محطة متوسطة -->
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center shadow-lg border-3 border-white dark:border-gray-800">
                                        <span class="text-white font-bold text-sm">{{ $index + 1 }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- معلومات المحطة -->
                            <div class="mr-6 flex-1">
                                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow duration-200">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-2">
                                                @if($index === 0)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                        نقطة البداية
                                                    </span>
                                                @elseif($index === $route->stops->count() - 1)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                        نقطة النهاية
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                        محطة رقم {{ $index + 1 }}
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">{{ $stop->name }}</h5>
                                            
                                            @if($stop->address)
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2 flex items-center">
                                                    <x-heroicon-o-map-pin class="w-4 h-4 ml-1" />
                                                    {{ $stop->address }}
                                                </p>
                                            @endif
                                            
                                            @if($stop->description)
                                                <p class="text-sm text-gray-500 dark:text-gray-500 flex items-center">
                                                    <x-heroicon-o-information-circle class="w-4 h-4 ml-1" />
                                                    {{ $stop->description }}
                                                </p>
                                            @endif
                                        </div>

                                        <!-- معلومات إضافية -->
                                        <div class="text-left">
                                            @if($stop->latitude && $stop->longitude)
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    <p>{{ number_format($stop->latitude, 6) }}</p>
                                                    <p>{{ number_format($stop->longitude, 6) }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- شريط التقدم للمحطات المتوسطة -->
                                    @if($index > 0 && $index < $route->stops->count() - 1)
                                        <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600">
                                            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                                <span>التقدم في المسار</span>
                                                <span>{{ round(($index / ($route->stops->count() - 1)) * 100) }}%</span>
                                            </div>
                                            <div class="mt-1 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                <div class="bg-gradient-to-r from-green-500 to-blue-500 h-2 rounded-full transition-all duration-500" 
                                                     style="width: {{ round(($index / ($route->stops->count() - 1)) * 100) }}%"></div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- خط الاتصال بين المحطات -->
                        @if($index < $route->stops->count() - 1)
                            <div class="relative flex items-center justify-center py-2">
                                <div class="flex items-center text-gray-400 dark:text-gray-500">
                                    <x-heroicon-o-chevron-down class="w-5 h-5 animate-bounce" />
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                <!-- ملخص المسار -->
                <div class="mt-8 bg-gradient-to-r from-blue-50 to-green-50 dark:from-gray-800 dark:to-gray-700 rounded-lg p-4 border border-blue-200 dark:border-gray-600">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4 rtl:space-x-reverse">
                            <div class="text-center">
                                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $route->stops->count() }}</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">إجمالي المحطات</p>
                            </div>
                            <div class="w-px h-8 bg-gray-300 dark:bg-gray-600"></div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-green-600 dark:text-green-400">1</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">نقطة بداية</p>
                            </div>
                            <div class="w-px h-8 bg-gray-300 dark:bg-gray-600"></div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-red-600 dark:text-red-400">1</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">نقطة نهاية</p>
                            </div>
                        </div>
                        
                        <div class="text-left">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">حالة المسار</p>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                <span class="w-2 h-2 bg-green-400 rounded-full ml-1 animate-pulse"></span>
                                مُكتمل
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- حالة عدم وجود محطات -->
            <div class="text-center py-12">
                <div class="w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <x-heroicon-o-map-pin class="w-12 h-12 text-gray-400" />
                </div>
                <h5 class="text-lg font-medium text-gray-900 dark:text-white mb-2">لا توجد محطات محددة</h5>
                <p class="text-gray-500 dark:text-gray-400 mb-4">لم يتم تحديد أي محطات لهذا المسار بعد</p>
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 max-w-md mx-auto">
                    <div class="flex items-center">
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-yellow-600 dark:text-yellow-400 ml-2" />
                        <p class="text-sm text-yellow-800 dark:text-yellow-200">
                            قم بإضافة محطات للمسار لعرض مخطط الرحلة
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- أنماط CSS مخصصة -->
<style>
    @keyframes slideInFromRight {
        from {
            opacity: 0;
            transform: translateX(20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .route-stop-card {
        animation: slideInFromRight 0.6s ease-out forwards;
    }

    .route-stop-card:nth-child(odd) {
        animation-delay: 0.1s;
    }

    .route-stop-card:nth-child(even) {
        animation-delay: 0.2s;
    }

    /* تأثيرات hover للنود */
    .route-node:hover {
        transform: scale(1.1);
        transition: transform 0.2s ease-in-out;
    }

    /* تدرج الألوان للخط الرئيسي */
    .route-line {
        background: linear-gradient(180deg, 
            #10b981 0%,    /* أخضر للبداية */
            #3b82f6 50%,   /* أزرق للمنتصف */
            #ef4444 100%   /* أحمر للنهاية */
        );
    }

    /* تأثير النبض للنقاط النشطة */
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }

    .animate-pulse-slow {
        animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    /* تصميم مسؤول للشاشات الصغيرة */
    @media (max-width: 768px) {
        .route-container {
            padding: 1rem;
        }
        
        .route-node {
            width: 12px;
            height: 12px;
        }
        
        .route-node.start,
        .route-node.end {
            width: 14px;
            height: 14px;
        }
    }
</style>
