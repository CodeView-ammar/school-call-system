@once
    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .leaflet-container {
            font-family: inherit;
        }
    </style>
    @endpush
    
    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @endpush
@endonce

<div class="space-y-4">
    <!-- معلومات الموقع -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 rounded-lg">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">خط العرض</label>
            <input 
                type="text" 
                id="map-latitude-display" 
                readonly
                class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-sm"
                placeholder="سيتم تحديده من الخريطة"
                value="24.7136"
            >
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">خط الطول</label>
            <input 
                type="text" 
                id="map-longitude-display" 
                readonly
                class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-sm"
                placeholder="سيتم تحديده من الخريطة"
                value="46.6753"
            >
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">العنوان</label>
            <input 
                type="text" 
                id="map-address-display" 
                readonly
                class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-sm"
                placeholder="سيتم جلبه من الخريطة"
            >
        </div>
    </div>

    <!-- أدوات البحث -->
    <div class="flex gap-2">
        <input 
            type="text" 
            id="map-search-input"
            placeholder="ابحث عن موقع (مثال: الرياض، حي النرجس)"
            class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm"
        >
        <button 
            type="button"
            onclick="searchMapLocation()"
            class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 text-sm"
        >
            بحث
        </button>
        <button 
            type="button"
            onclick="getCurrentMapLocation()"
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm"
        >
            موقعي الحالي
        </button>
    </div>

    <!-- الخريطة -->
    <div 
        id="branch-location-map"
        style="height: 400px"
        class="w-full border border-gray-300 rounded-lg"
    ></div>

    <!-- تعليمات -->
    <div class="text-sm text-gray-600 bg-blue-50 p-3 rounded-lg">
        <strong>كيفية الاستخدام:</strong>
        <ul class="mt-1 list-disc list-inside space-y-1">
            <li>انقر على الخريطة لتحديد موقع الفرع</li>
            <li>استخدم صندوق البحث للعثور على موقع محدد</li>
            <li>انقر على "موقعي الحالي" لاستخدام موقعك الحالي</li>
            <li>يمكنك سحب العلامة لتعديل الموقع</li>
        </ul>
    </div>
</div>

<script>
let branchMap, branchMarker;

document.addEventListener('DOMContentLoaded', function() {
    initBranchMap();
});

function initBranchMap() {
    if (typeof L === 'undefined') {
        setTimeout(initBranchMap, 100);
        return;
    }

    const mapContainer = document.getElementById('branch-location-map');
    if (!mapContainer || branchMap) return;

    // إنشاء الخريطة
    branchMap = L.map('branch-location-map').setView([24.7136, 46.6753], 13);

    // إضافة طبقة الخريطة
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(branchMap);

    // إضافة العلامة
    branchMarker = L.marker([24.7136, 46.6753], {
        draggable: true,
        title: 'موقع الفرع'
    }).addTo(branchMap);

    // أحداث الخريطة
    branchMap.on('click', function(e) {
        updateBranchMarkerPosition(e.latlng.lat, e.latlng.lng);
    });

    branchMarker.on('dragend', function(e) {
        const position = e.target.getLatLng();
        updateBranchMarkerPosition(position.lat, position.lng);
    });

    // جلب العنوان الأولي
    reverseGeocodeBranch(24.7136, 46.6753);
}

function updateBranchMarkerPosition(lat, lng) {
    branchMarker.setLatLng([lat, lng]);
    branchMap.setView([lat, lng]);
    
    // تحديث العرض
    document.getElementById('map-latitude-display').value = lat.toFixed(6);
    document.getElementById('map-longitude-display').value = lng.toFixed(6);
    
    // تحديث الحقول الفعلية في النموذج
    const latInput = document.querySelector('input[name="latitude"]');
    const lngInput = document.querySelector('input[name="longitude"]');
    
    if (latInput) {
        latInput.value = lat;
        latInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
    
    if (lngInput) {
        lngInput.value = lng;
        lngInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
    
    reverseGeocodeBranch(lat, lng);
}

function reverseGeocodeBranch(lat, lng) {
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=ar`)
        .then(response => response.json())
        .then(data => {
            const address = data?.display_name || `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
            document.getElementById('map-address-display').value = address;
        })
        .catch(() => {
            document.getElementById('map-address-display').value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        });
}

function searchMapLocation() {
    const query = document.getElementById('map-search-input').value.trim();
    if (!query) return;

    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&accept-language=ar&limit=1`)
        .then(response => response.json())
        .then(data => {
            if (data && data.length > 0) {
                const result = data[0];
                const lat = parseFloat(result.lat);
                const lng = parseFloat(result.lon);
                updateBranchMarkerPosition(lat, lng);
                branchMap.setZoom(15);
            } else {
                alert('لم يتم العثور على الموقع. يرجى المحاولة مرة أخرى.');
            }
        })
        .catch(() => {
            alert('حدث خطأ أثناء البحث. يرجى المحاولة مرة أخرى.');
        });
}

function getCurrentMapLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                updateBranchMarkerPosition(lat, lng);
                branchMap.setZoom(16);
            },
            function(error) {
                alert('لا يمكن تحديد موقعك الحالي.');
            }
        );
    } else {
        alert('متصفحك لا يدعم تحديد الموقع.');
    }
}
</script>