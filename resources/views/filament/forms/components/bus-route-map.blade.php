
<div class="space-y-4">
    @once
        @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
              integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
              crossorigin="" />
        @endpush
        
        @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" 
                integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" 
                crossorigin=""></script>
        @endpush
    @endonce

    <!-- معلومات المواقع -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-50 rounded-lg">
        <!-- نقطة البداية -->
        <div class="space-y-2">
            <h4 class="text-sm font-medium text-gray-700 border-b pb-1">نقطة البداية (من)</h4>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs text-gray-600 mb-1">خط العرض</label>
                    <input 
                        type="text" 
                        id="route-from-lat-display" 
                        readonly
                        class="w-full px-2 py-1 border border-gray-300 rounded text-xs bg-white"
                        placeholder="سيتم تحديده من الخريطة"
                        value="24.7136"
                    >
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">خط الطول</label>
                    <input 
                        type="text" 
                        id="route-from-lng-display" 
                        readonly
                        class="w-full px-2 py-1 border border-gray-300 rounded text-xs bg-white"
                        placeholder="سيتم تحديده من الخريطة"
                        value="46.6753"
                    >
                </div>
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-1">العنوان</label>
                <input 
                    type="text" 
                    id="route-from-address-display" 
                    readonly
                    class="w-full px-2 py-1 border border-gray-300 rounded text-xs bg-white"
                    placeholder="سيتم جلبه من الخريطة"
                >
            </div>
        </div>

        <!-- نقطة النهاية -->
        <div class="space-y-2">
            <h4 class="text-sm font-medium text-gray-700 border-b pb-1">نقطة النهاية (إلى)</h4>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs text-gray-600 mb-1">خط العرض</label>
                    <input 
                        type="text" 
                        id="route-to-lat-display" 
                        readonly
                        class="w-full px-2 py-1 border border-gray-300 rounded text-xs bg-white"
                        placeholder="سيتم تحديده من الخريطة"
                        value="24.7500"
                    >
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">خط الطول</label>
                    <input 
                        type="text" 
                        id="route-to-lng-display" 
                        readonly
                        class="w-full px-2 py-1 border border-gray-300 rounded text-xs bg-white"
                        placeholder="سيتم تحديده من الخريطة"
                        value="46.7000"
                    >
                </div>
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-1">العنوان</label>
                <input 
                    type="text" 
                    id="route-to-address-display" 
                    readonly
                    class="w-full px-2 py-1 border border-gray-300 rounded text-xs bg-white"
                    placeholder="سيتم جلبه من الخريطة"
                >
            </div>
        </div>
    </div>

    <!-- أدوات التحكم -->
    <div class="flex flex-wrap gap-2 items-center">
        <input 
            type="text" 
            id="route-search-input"
            placeholder="ابحث عن موقع (مثال: الرياض، حي النرجس)"
            class="flex-1 min-w-64 px-3 py-2 border border-gray-300 rounded-md text-sm"
        >
        <button 
            type="button"
            onclick="searchRouteLocation()"
            class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 text-sm"
        >
            بحث
        </button>
        <button 
            type="button"
            onclick="getCurrentRouteLocation()"
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm"
        >
            موقعي الحالي
        </button>
        <button 
            type="button"
            onclick="clearRouteMap()"
            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm"
        >
            مسح المسار
        </button>
    </div>

    <!-- نوع النقطة المحددة -->
    <div class="flex gap-4 p-3 bg-blue-50 rounded-lg">
        <label class="flex items-center text-sm">
            <input type="radio" name="route-point-type" value="from" checked class="mr-2">
            تحديد نقطة البداية (من)
        </label>
        <label class="flex items-center text-sm">
            <input type="radio" name="route-point-type" value="to" class="mr-2">
            تحديد نقطة النهاية (إلى)
        </label>
    </div>

    <!-- الخريطة -->
    <div 
        id="bus-route-map"
        style="height: 500px"
        class="w-full border border-gray-300 rounded-lg"
    ></div>

    <!-- تعليمات -->
    <div class="text-sm text-gray-600 bg-blue-50 p-3 rounded-lg">
        <strong>كيفية الاستخدام:</strong>
        <ul class="mt-1 list-disc list-inside space-y-1">
            <li>اختر نوع النقطة أولاً (من أو إلى) ثم انقر على الخريطة لتحديد الموقع</li>
            <li>استخدم صندوق البحث للعثور على موقع محدد</li>
            <li>انقر على "موقعي الحالي" لاستخدام موقعك الحالي</li>
            <li>يمكنك سحب العلامات لتعديل المواقع</li>
            <li>النقطة الخضراء: نقطة البداية - النقطة الحمراء: نقطة النهاية</li>
            <li>الخط الأزرق يوضح المسار بين النقطتين</li>
        </ul>
    </div>
</div>

<script>
let routeMap, fromMarker, toMarker, routeLine;
const busSelect = document.querySelector('#data\\.bus_id');

// إضافة حدث لتحديث الخريطة عند اختيار باص
if (busSelect) {
    busSelect.addEventListener('change', function () {
        
        initBusRouteMap(); // استدعاء دالة تحميل الخريطة
    });
}
document.addEventListener("DOMContentLoaded", function () {
    initBusRouteMap();
});
function initBusRouteMap() {
    if (typeof L === 'undefined') {
        setTimeout(initBusRouteMap, 100);
        return;
    }

    const mapContainer = document.getElementById('bus-route-map');
    if (!mapContainer || routeMap) return;

    // إنشاء الخريطة
    routeMap = L.map('bus-route-map').setView([24.7136, 46.6753], 12);

    // إضافة طبقة الخريطة
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(routeMap);

    // إضافة العلامات الأولية
    fromMarker = L.marker([24.7136, 46.6753], {
        draggable: true,
        title: 'نقطة البداية',
        icon: L.icon({
            iconUrl: 'data:image/svg+xml;base64,' + btoa(`
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="green" width="25" height="25">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                </svg>
            `),
            iconSize: [25, 25],
            iconAnchor: [12, 25]
        })
    }).addTo(routeMap);

    toMarker = L.marker([24.7500, 46.7000], {
        draggable: true,
        title: 'نقطة النهاية',
        icon: L.icon({
            iconUrl: 'data:image/svg+xml;base64,' + btoa(`
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="red" width="25" height="25">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                </svg>
            `),
            iconSize: [25, 25],
            iconAnchor: [12, 25]
        })
    }).addTo(routeMap);

    // إضافة خط المسار
    updateRouteLine();

    // أحداث الخريطة
    routeMap.on('click', function(e) {
        const pointType = document.querySelector('input[name="route-point-type"]:checked').value;
        if (pointType === 'from') {
            updateFromMarkerPosition(e.latlng.lat, e.latlng.lng);
        } else {
            updateToMarkerPosition(e.latlng.lat, e.latlng.lng);
        }
    });

    fromMarker.on('dragend', function(e) {
        const position = e.target.getLatLng();
        updateFromMarkerPosition(position.lat, position.lng);
    });

    toMarker.on('dragend', function(e) {
        const position = e.target.getLatLng();
        updateToMarkerPosition(position.lat, position.lng);
    });

    // جلب العناوين الأولية
    reverseGeocodeRoute(24.7136, 46.6753, 'from');
    reverseGeocodeRoute(24.7500, 46.7000, 'to');
}

function updateFromMarkerPosition(lat, lng) {
    fromMarker.setLatLng([lat, lng]);
    
    // تحديث العرض
// alert(" ")
    document.querySelector('input[name="data[route_road_from_lat]"]').value = 24.7136;
document.querySelector('input[name="data[route_road_from_lng]"]').value = 46.6753;
    // تحديث الحقول الفعلية في النموذج

    const latInput = document.querySelector('input[name="data[route_road_from_lat]"]');
    const lngInput = document.querySelector('input[name="data[route_road_from_lng]"]');
    
    if (latInput) {
        latInput.value = lat;
        latInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
    
    if (lngInput) {
        lngInput.value = lng;
        lngInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
    
    reverseGeocodeRoute(lat, lng, 'from');
    updateRouteLine();
}

function updateToMarkerPosition(lat, lng) {
    toMarker.setLatLng([lat, lng]);
    
    // تحديث العرض
    document.getElementById('route-to-lat-display').value = lat.toFixed(6);
    document.getElementById('route-to-lng-display').value = lng.toFixed(6);
    
    // تحديث الحقول الفعلية في النموذج
    
    const latInput = document.querySelector('input[name="route_road_to_lat"]');
    const lngInput = document.querySelector('input[name="route_road_to_lng"]');
    
    if (latInput) {
        latInput.value = lat;
        latInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
    
    if (lngInput) {
        lngInput.value = lng;
        lngInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
    
    reverseGeocodeRoute(lat, lng, 'to');
    updateRouteLine();
}

function updateRouteLine() {
    if (routeLine) {
        routeMap.removeLayer(routeLine);
    }
    
    const fromPos = fromMarker.getLatLng();
    const toPos = toMarker.getLatLng();
    
    routeLine = L.polyline([fromPos, toPos], {
        color: '#3b82f6',
        weight: 4,
        opacity: 0.7,
        dashArray: '10, 5'
    }).addTo(routeMap);
    
    // تحديث عرض الخريطة لتشمل كلا النقطتين
    const group = new L.featureGroup([fromMarker, toMarker]);
    routeMap.fitBounds(group.getBounds().pad(0.1));
}

function reverseGeocodeRoute(lat, lng, type) {
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=ar`)
        .then(response => response.json())
        .then(data => {
            const address = data?.display_name || `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
            document.getElementById(`route-${type}-address-display`).value = address;
            
            // تحديث الحقل الفعلي في النموذج
            const addressInput = document.querySelector(`input[name="route_road_${type}_address"]`);
            if (addressInput) {
                addressInput.value = address;
                addressInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        })
        .catch(() => {
            const address = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
            document.getElementById(`route-${type}-address-display`).value = address;
        });
}

function searchRouteLocation() {
    const query = document.getElementById('route-search-input').value.trim();
    if (!query) return;

    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&accept-language=ar&limit=1`)
        .then(response => response.json())
        .then(data => {
            if (data && data.length > 0) {
                const result = data[0];
                const lat = parseFloat(result.lat);
                const lng = parseFloat(result.lon);
                
                const pointType = document.querySelector('input[name="route-point-type"]:checked').value;
                if (pointType === 'from') {
                    updateFromMarkerPosition(lat, lng);
                } else {
                    updateToMarkerPosition(lat, lng);
                }
                
                routeMap.setView([lat, lng], 15);
            } else {
                alert('لم يتم العثور على الموقع. يرجى المحاولة مرة أخرى.');
            }
        })
        .catch(() => {
            alert('حدث خطأ أثناء البحث. يرجى المحاولة مرة أخرى.');
        });
}

function getCurrentRouteLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                const pointType = document.querySelector('input[name="route-point-type"]:checked').value;
                if (pointType === 'from') {
                    updateFromMarkerPosition(lat, lng);
                } else {
                    updateToMarkerPosition(lat, lng);
                }
                
                routeMap.setView([lat, lng], 16);
            },
            function(error) {
                alert('لا يمكن تحديد موقعك الحالي.');
            }
        );
    } else {
        alert('متصفحك لا يدعم تحديد الموقع.');
    }
}

function clearRouteMap() {
    // إعادة تعيين المواقع إلى القيم الافتراضية
    updateFromMarkerPosition(24.7136, 46.6753);
    updateToMarkerPosition(24.7500, 46.7000);
    
    // إعادة تعيين صندوق البحث
    document.getElementById('route-search-input').value = '';
    
    routeMap.setView([24.7136, 46.6753], 12);
}
</script>
