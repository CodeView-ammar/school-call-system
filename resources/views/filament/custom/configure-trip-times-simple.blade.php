<x-filament-panels::page>
    <div class="space-y-6"> <!-- Root div واحد -->

        <!-- Custom CSS for Trips -->
        <style>
            @import url('{{ asset('css/trips-custom.css') }}');
        </style>

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
                    <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->route->route_ar }}</div>
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

        <!-- Map Section -->
        <div class="bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 rounded-lg overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
                        <x-heroicon-o-map class="w-4 h-4 text-white" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white">خريطة المسار</h3>
                        <p class="text-sm text-blue-100">عرض المحطات والمسار على الخريطة</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 bg-red-500 rounded-full"></span>
                        <span class="text-sm text-gray-600">نقطة البداية</span>
                        <span class="w-3 h-3 bg-blue-500 rounded-full ml-4"></span>
                        <span class="text-sm text-gray-600">محطات المسار</span>
                        <span class="w-3 h-3 bg-green-500 rounded-full ml-4"></span>
                        <span class="text-sm text-gray-600">نقطة النهاية</span>
                    </div>
                    <button id="fitMapButton" class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded border">
                        ملائمة العرض
                    </button>
                </div>

                <div class="relative">
                    <div id="tripMap" class="w-full h-96 bg-gray-100 dark:bg-gray-800 rounded-lg"></div>
                    <div id="mapLoadingIndicator" class="absolute inset-0 flex items-center justify-center bg-gray-100 dark:bg-gray-800 rounded-lg">
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                            <span class="text-sm text-gray-600">جاري تحميل الخريطة...</span>
                        </div>
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
                                        <button 
                                            wire:click="subtractMinutesFromStop({{ $tripStop['stop_id'] }}, 5)"
                                            class="time-control-btn decrease"
                                            title="تقليل 5 دقائق"
                                        >
                                            <x-heroicon-o-minus class="w-4 h-4" />
                                        </button>

                                        <div class="time-display">
                                            <span>{{ \Carbon\Carbon::parse($tripStop['arrival_time'])->format('H:i') }}</span>
                                        </div>

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
    </div> <!-- نهاية root div -->
</x-filament-panels::page>
    <!-- Scripts -->
<script>
    let tripMap;
    let tripMarkers = [];
    let routePath;

    // إزالة مؤشر التحميل
    function hideLoadingIndicator() {
        const indicator = document.getElementById('mapLoadingIndicator');
        if (indicator) {
            indicator.style.display = 'none';
        }
    }

    window.initializeTripMap = function() {
        const mapElement = document.getElementById('tripMap');
        if (!mapElement) return;

        tripMap = new google.maps.Map(mapElement, {
            center: { lat: 24.7136, lng: 46.6753 },
            zoom: 12,
            styles: [
                { featureType: "poi", elementType: "labels", stylers: [{ visibility: "off" }] },
                { featureType: "transit", elementType: "labels", stylers: [{ visibility: "off" }] }
            ]
        });

        hideLoadingIndicator();
        loadTripStops();
        setupFitButton();
    }

    function loadTripStops() {
        console.log('Loading trip stops...');
        const stops = @json($tripStops ?? []);
        console.log('Trip stops data:', stops);
        
        if (!stops || stops.length === 0) {
            console.log('No stops found, adding sample data for testing...');
            // إضافة بيانات تجريبية للاختبار
            addSampleStops();
            return;
        }

        drawStopsOnMap(stops);
    }

    function setupFitButton() {
        document.getElementById('fitMapButton')?.addEventListener('click', function() {
            if (tripMarkers.length > 0) {
                const bounds = new google.maps.LatLngBounds();
                tripMarkers.forEach(marker => bounds.extend(marker.getPosition()));
                tripMap.fitBounds(bounds);
                if (tripMarkers.length === 1) tripMap.setZoom(16);
            }
        });
    }

    document.addEventListener('livewire:init', () => {
        Livewire.on('stop-time-updated', (event) => {
            setTimeout(() => { clearMapElements(); loadTripStops(); }, 500);
        });
        Livewire.on('times-reset', (event) => {
            setTimeout(() => { clearMapElements(); loadTripStops(); }, 500);
        });
    });

    function clearMapElements() {
        tripMarkers.forEach(marker => marker.setMap(null));
        tripMarkers = [];
        if (routePath) { 
            routePath.setMap(null); 
            routePath = null; 
        }
    }

    // إضافة محطات تجريبية للاختبار
    function addSampleStops() {
        const sampleStops = [
            {
                stop: {
                    id: 1,
                    name: 'محطة الملك فهد',
                    latitude: 24.7136,
                    longitude: 46.6753,
                    address: 'شارع الملك فهد، الرياض'
                },
                arrival_time: '07:00'
            },
            {
                stop: {
                    id: 2,
                    name: 'محطة العليا',
                    latitude: 24.7341,
                    longitude: 46.6756,
                    address: 'حي العليا، الرياض'
                },
                arrival_time: '07:15'
            },
            {
                stop: {
                    id: 3,
                    name: 'محطة الملز',
                    latitude: 24.6877,
                    longitude: 46.7219,
                    address: 'حي الملز، الرياض'
                },
                arrival_time: '07:30'
            }
        ];

        console.log('Adding sample stops to map:', sampleStops);
        drawStopsOnMap(sampleStops);
    }

    function drawStopsOnMap(stops) {
        const bounds = new google.maps.LatLngBounds();

        stops.forEach((tripStop, index) => {
            const stop = tripStop.stop;
            const position = new google.maps.LatLng(
                parseFloat(stop.latitude), 
                parseFloat(stop.longitude)
            );

            let markerColor = 'blue';
            if (index === 0) markerColor = 'red';
            else if (index === stops.length - 1) markerColor = 'green';

            const marker = new google.maps.Marker({
                position: position,
                map: tripMap,
                title: `${stop.name} - وقت الوصول: ${tripStop.arrival_time}`,
                icon: {
                    url: `https://maps.google.com/mapfiles/ms/icons/${markerColor}-dot.png`,
                    scaledSize: new google.maps.Size(32, 32)
                }
            });

            const infoWindow = new google.maps.InfoWindow({
                content: `
                    <div style="padding:8px;min-width:200px;">
                        <h4 style="margin:0 0 8px 0;color:#1f2937;font-size:14px;font-weight:600;">محطة ${index+1}</h4>
                        <p style="margin:0 0 4px 0;color:#374151;font-size:13px;"><strong>${stop.name}</strong></p>
                        <p style="margin:0 0 4px 0;color:#6b7280;font-size:12px;">${stop.address||'عنوان غير محدد'}</p>
                        <p style="margin:0;color:#059669;font-size:12px;font-weight:600;">⏰ وقت الوصول: ${tripStop.arrival_time}</p>
                    </div>`
            });

            marker.addListener('click', () => infoWindow.open(tripMap, marker));
            tripMarkers.push(marker);
            bounds.extend(position);
        });

        // رسم المسار الفعلي باستخدام Directions API
        if (stops.length > 1) {
            drawDrivingRoute(stops);
        }

        if (stops.length > 0) {
            tripMap.fitBounds(bounds);
            if (stops.length === 1) tripMap.setZoom(16);
        }
    }

    function drawDrivingRoute(stops) {
        const directionsService = new google.maps.DirectionsService();
        const directionsRenderer = new google.maps.DirectionsRenderer({
            suppressMarkers: true, // لا نريد إظهار العلامات الافتراضية
            polylineOptions: {
                strokeColor: '#f97316',
                strokeOpacity: 1.0,
                strokeWeight: 4
            }
        });

        directionsRenderer.setMap(tripMap);

        // إعداد نقاط التوقف
        const waypoints = [];
        for (let i = 1; i < stops.length - 1; i++) {
            waypoints.push({
                location: new google.maps.LatLng(
                    parseFloat(stops[i].stop.latitude),
                    parseFloat(stops[i].stop.longitude)
                ),
                stopover: true
            });
        }

        const origin = new google.maps.LatLng(
            parseFloat(stops[0].stop.latitude),
            parseFloat(stops[0].stop.longitude)
        );

        const destination = new google.maps.LatLng(
            parseFloat(stops[stops.length - 1].stop.latitude),
            parseFloat(stops[stops.length - 1].stop.longitude)
        );

        const request = {
            origin: origin,
            destination: destination,
            waypoints: waypoints,
            travelMode: google.maps.TravelMode.DRIVING,
            optimizeWaypoints: false, // الحفاظ على ترتيب المحطات
            avoidHighways: false,
            avoidTolls: false
        };

        directionsService.route(request, (result, status) => {
            if (status === 'OK') {
                directionsRenderer.setDirections(result);
                
                // حفظ مرجع المسار للتنظيف لاحقاً
                if (routePath) {
                    routePath.setMap(null);
                }
                routePath = directionsRenderer;

                // عرض معلومات إضافية عن المسار
                const route = result.routes[0];
                let totalDistance = 0;
                let totalDuration = 0;

                route.legs.forEach(leg => {
                    totalDistance += leg.distance.value;
                    totalDuration += leg.duration.value;
                });

                console.log(`إجمالي المسافة: ${(totalDistance / 1000).toFixed(1)} كم`);
                console.log(`إجمالي الوقت المقدر: ${Math.round(totalDuration / 60)} دقيقة`);
            } else {
                console.error('فشل في رسم المسار:', status);
                // في حالة فشل Directions API، نعود للخط المستقيم
                drawStraightLineRoute(stops);
            }
        });
    }

    function drawStraightLineRoute(stops) {
        const routeCoordinates = stops.map(tripStop => 
            new google.maps.LatLng(
                parseFloat(tripStop.stop.latitude),
                parseFloat(tripStop.stop.longitude)
            )
        );

        routePath = new google.maps.Polyline({ 
            path: routeCoordinates, 
            geodesic: true, 
            strokeColor:'#f97316', 
            strokeOpacity:1.0, 
            strokeWeight:4 
        });
        routePath.setMap(tripMap);
    }

    // تهيئة تلقائية للخريطة
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, initializing map...');
        
        // إضافة Google Maps API
        const script = document.createElement('script');
        script.src = 'https://maps.googleapis.com/maps/api/js?key=AIzaSyA903FiEEzDSEmogbe9-PkmA_v520gnrQ4&libraries=geometry&callback=initializeTripMap';
        script.async = true;
        script.defer = true;
        
        // التعامل مع أخطاء التحميل
        script.onerror = function() {
            console.error('فشل في تحميل Google Maps API');
            const indicator = document.getElementById('mapLoadingIndicator');
            if (indicator) {
                indicator.innerHTML = '<div class="text-red-500">خطأ في تحميل الخريطة</div>';
            }
        };
        
        document.head.appendChild(script);
        console.log('Google Maps script added to head');
    });
    </script>
    

