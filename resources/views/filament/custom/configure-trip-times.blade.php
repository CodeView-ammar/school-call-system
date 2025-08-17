<x-filament-panels::page>
    <!-- Custom CSS for Trips -->
    <link rel="stylesheet" href="{{ asset('css/trips-custom.css') }}">
    
    <div class="space-y-6">
        <!-- Trip Information Header -->
        <div class="bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 rounded-lg p-6">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 bg-orange-100 dark:bg-orange-900/20 rounded-full">
                            <x-heroicon-o-information-circle class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">معلومات الرحلة</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">اضبط المسار والتاريخ والسائق</p>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                        {{ $record->is_active ? 'نشط' : 'غير نشط' }}
                    </span>
                </div>
            </div>
            
            <!-- Trip Details Grid -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">المسار</div>
                    <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->route->name }}</div>
                </div>
                
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">تاريخ البدء</div>
                    <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->effective_date->format('Y-m-d') }}</div>
                </div>
                
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">وقت البدء</div>
                    <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->arrival_time_at_first_stop }}</div>
                </div>
                
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">التكرار</div>
                    <div class="mt-1 text-sm text-gray-900 dark:text-white">
                        @switch($record->repeated_every_days)
                            @case(1) يومياً @break
                            @case(7) أسبوعياً @break  
                            @case(14) كل أسبوعين @break
                            @case(30) شهرياً @break
                            @default كل {{ $record->repeated_every_days }} أيام
                        @endswitch
                    </div>
                </div>
            </div>
        </div>

        <!-- Configure Time Table Section -->
        <div class="bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 rounded-lg overflow-hidden">
            <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="step-indicator">
                        <span>2</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white">تكوين جدول الأوقات</h3>
                        <p class="text-sm text-orange-100">اضبط وقت الوصول لكل محطة</p>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 min-h-[600px]">
                <!-- Stops List -->
                <div class="p-6 border-b lg:border-b-0 lg:border-r border-gray-200 dark:border-gray-700">
                    <div class="space-y-4">
                        @foreach($tripStops as $index => $tripStop)
                            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 transition-all duration-200 hover:shadow-md">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="stop-order">
                                            <span>{{ $index + 1 }}</span>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-900 dark:text-white">محطة {{ $index + 1 }}</h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $tripStop['stop']['name'] ?? 'محطة غير محددة' }}</p>
                                            @if(!empty($tripStop['stop']['address']))
                                                <p class="text-xs text-gray-500 dark:text-gray-500">{{ $tripStop['stop']['address'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Arrival Time Controls -->
                                <div class="mt-4 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">وقت الوصول</span>
                                    </div>
                                    
                                    <div class="flex items-center gap-2">
                                        <!-- Decrease Time Button -->
                                        <button 
                                            wire:click="subtractMinutesFromStop({{ $tripStop['stop_id'] }}, 5)"
                                            class="time-control-btn decrease"
                                            title="تقليل 5 دقائق"
                                        >
                                            <x-heroicon-o-minus class="w-4 h-4" />
                                        </button>

                                        <!-- Time Display -->
                                        <div class="time-display">
                                            <span>{{ \Carbon\Carbon::parse($tripStop['arrival_time'])->format('H:i') }}</span>
                                        </div>

                                        <!-- Increase Time Button -->
                                        <button 
                                            wire:click="addMinutesToStop({{ $tripStop['stop_id'] }}, 5)"
                                            class="time-control-btn increase"
                                            title="زيادة 5 دقائق"
                                        >
                                            <x-heroicon-o-plus class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>

                                @if($index > 0)
                                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-500">
                                        الاتجاه: {{ $tripStop['stop']['address'] ?? 'غير محدد' }}
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        @if(empty($tripStops))
                            <div class="text-center py-8">
                                <x-heroicon-o-map-pin class="w-12 h-12 text-gray-400 mx-auto mb-3" />
                                <p class="text-gray-500 dark:text-gray-400">لا توجد محطات محددة لهذا المسار</p>
                                <p class="text-sm text-gray-400 dark:text-gray-500">قم بإضافة محطات للمسار أولاً</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Map Section -->
                <div class="relative">
                    <div class="absolute top-4 right-4 z-10">
                        <button 
                            id="fitMapButton"
                            class="fit-button"
                            title="ملائمة المسار في الخريطة"
                        >
                            <x-heroicon-o-arrows-pointing-out class="w-5 h-5" />
                        </button>
                    </div>
                    
                    <div id="tripMap" class="w-full h-full min-h-[600px] bg-gray-100 dark:bg-gray-800"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Google Maps Integration -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA903FiEEzDSEmogbe9-PkmA_v520gnrQ4&libraries=geometry" async defer></script>
    <script>
        let tripMap;
        let tripMarkers = [];
        let routePath;

        document.addEventListener('DOMContentLoaded', function () {
            initializeTripMap();
        });

        function initializeTripMap() {
            const mapElement = document.getElementById('tripMap');
            if (!mapElement) return;

            tripMap = new google.maps.Map(mapElement, {
                center: { lat: 24.7136, lng: 46.6753 }, // الرياض
                zoom: 12,
                styles: [
                    {
                        featureType: "poi",
                        elementType: "labels",
                        stylers: [{ visibility: "off" }]
                    }
                ]
            });

            loadTripStops();
            setupFitButton();
        }

        function loadTripStops() {
            const stops = @json($tripStops ?? []);
            
            if (stops.length === 0) {
                return;
            }

            // إنشاء العلامات
            const bounds = new google.maps.LatLngBounds();
            const routeCoordinates = [];

            stops.forEach((tripStop, index) => {
                const stop = tripStop.stop;
                if (!stop.latitude || !stop.longitude) return;

                const position = new google.maps.LatLng(
                    parseFloat(stop.latitude), 
                    parseFloat(stop.longitude)
                );

                // تحديد لون العلامة
                let markerColor = 'blue';
                if (index === 0) markerColor = 'red'; // أول محطة
                else if (index === stops.length - 1) markerColor = 'green'; // آخر محطة

                const marker = new google.maps.Marker({
                    position: position,
                    map: tripMap,
                    title: `${stop.name} - وقت الوصول: ${tripStop.arrival_time}`,
                    icon: {
                        url: `https://maps.google.com/mapfiles/ms/icons/${markerColor}-dot.png`,
                        scaledSize: new google.maps.Size(32, 32)
                    }
                });

                // إضافة نافذة معلومات
                const infoWindow = new google.maps.InfoWindow({
                    content: `
                        <div style="padding: 8px; min-width: 200px;">
                            <h4 style="margin: 0 0 8px 0; color: #1f2937; font-size: 14px; font-weight: 600;">محطة ${index + 1}</h4>
                            <p style="margin: 0 0 4px 0; color: #374151; font-size: 13px;"><strong>${stop.name}</strong></p>
                            <p style="margin: 0 0 4px 0; color: #6b7280; font-size: 12px;">${stop.address || 'عنوان غير محدد'}</p>
                            <p style="margin: 0; color: #059669; font-size: 12px; font-weight: 600;">⏰ وقت الوصول: ${tripStop.arrival_time}</p>
                        </div>
                    `
                });

                marker.addListener('click', () => {
                    infoWindow.open(tripMap, marker);
                });

                tripMarkers.push(marker);
                bounds.extend(position);
                routeCoordinates.push(position);
            });

            // رسم المسار
            if (routeCoordinates.length > 1) {
                routePath = new google.maps.Polyline({
                    path: routeCoordinates,
                    geodesic: true,
                    strokeColor: '#f97316', // أورانج
                    strokeOpacity: 1.0,
                    strokeWeight: 4
                });
                routePath.setMap(tripMap);
            }

            // ملائمة الخريطة لإظهار جميع النقاط
            if (stops.length > 0) {
                tripMap.fitBounds(bounds);
                if (stops.length === 1) {
                    tripMap.setZoom(16);
                }
            }
        }

        function setupFitButton() {
            document.getElementById('fitMapButton')?.addEventListener('click', function() {
                if (tripMarkers.length > 0) {
                    const bounds = new google.maps.LatLngBounds();
                    tripMarkers.forEach(marker => bounds.extend(marker.getPosition()));
                    tripMap.fitBounds(bounds);
                    if (tripMarkers.length === 1) {
                        tripMap.setZoom(16);
                    }
                }
            });
        }

        // الاستماع لأحداث Livewire
        document.addEventListener('livewire:init', () => {
            Livewire.on('stop-time-updated', (event) => {
                // يمكن إضافة animation أو تحديث الواجهة هنا
                console.log('Stop time updated:', event);
                
                // إعادة تحميل العلامات لتحديث أوقات الوصول
                setTimeout(() => {
                    clearMapElements();
                    loadTripStops();
                }, 500);
            });

            Livewire.on('times-reset', (event) => {
                console.log('Times reset:', event);
                setTimeout(() => {
                    clearMapElements();
                    loadTripStops();
                }, 500);
            });
        });

        function clearMapElements() {
            // إزالة العلامات
            tripMarkers.forEach(marker => marker.setMap(null));
            tripMarkers = [];
            
            // إزالة المسار
            if (routePath) {
                routePath.setMap(null);
                routePath = null;
            }
        }

        // CSS للانيميشن
        const style = document.createElement('style');
        style.textContent = `
            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.5; }
            }
            .animate-pulse {
                animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            }
        `;
        document.head.appendChild(style);
    </script>

    <!-- Livewire Scripts -->
    @livewireScripts
</x-filament-panels::page>