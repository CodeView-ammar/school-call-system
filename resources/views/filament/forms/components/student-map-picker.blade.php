
<div class="student-map-picker-wrapper">
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">
            موقع الطالب على الخريطة
        </label>
        <p class="text-sm text-gray-500 mb-4">
            انقر على الخريطة لتحديد موقع سكن الطالب
        </p>
    </div>
    
    <div id="student-map-picker-{{ $getId() ?? 'default' }}" 
         style="height: 400px; width: 100%; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 16px;"
         x-data="studentMapPicker('{{ $getId() ?? 'default' }}')"
         x-init="initMap()"
         wire:ignore>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
        <div class="p-3 bg-gray-50 rounded-lg">
            <div class="text-sm font-medium text-gray-700">خط العرض</div>
            <div class="text-lg font-mono text-gray-900" x-text="selectedLat || 'غير محدد'"></div>
        </div>
        <div class="p-3 bg-gray-50 rounded-lg">
            <div class="text-sm font-medium text-gray-700">خط الطول</div>
            <div class="text-lg font-mono text-gray-900" x-text="selectedLng || 'غير محدد'"></div>
        </div>
        <div class="p-3 bg-gray-50 rounded-lg">
            <div class="text-sm font-medium text-gray-700">الحالة</div>
            <div class="text-sm" x-text="selectedLat && selectedLng ? 'تم تحديد الموقع' : 'لم يتم تحديد الموقع'"></div>
        </div>
    </div>
    
    <div class="mt-4 flex gap-2">
        <button type="button" 
                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors"
                @click="getCurrentLocation()">
            📍 استخدام الموقع الحالي
        </button>
        <button type="button" 
                class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors"
                @click="clearLocation()">
            🗑️ مسح الموقع
        </button>
    </div>
</div>

<script>
function studentMapPicker(componentId) {
    return {
        map: null,
        marker: null,
        selectedLat: null,
        selectedLng: null,
        componentId: componentId,

        initMap() {
            // إحداثيات الرياض الافتراضية
            const defaultLat = 24.7136;
            const defaultLng = 46.6753;

            // إنشاء الخريطة
            this.map = L.map(`student-map-picker-${this.componentId}`).setView([defaultLat, defaultLng], 11);

            // إضافة طبقة الخريطة
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(this.map);

            // إضافة حدث النقر على الخريطة
            this.map.on('click', (e) => {
                this.selectLocation(e.latlng.lat, e.latlng.lng);
            });

            // محاولة تحميل الموقع المحفوظ مسبقاً
            this.loadSavedLocation();
        },

        selectLocation(lat, lng) {
            this.selectedLat = lat.toFixed(6);
            this.selectedLng = lng.toFixed(6);

            // إزالة العلامة السابقة
            if (this.marker) {
                this.map.removeLayer(this.marker);
            }

            // إضافة علامة جديدة
            this.marker = L.marker([lat, lng])
                .addTo(this.map)
                .bindPopup('موقع الطالب')
                .openPopup();

            // إرسال البيانات إلى Livewire
            if (window.Livewire) {
                // alert(this.selectedLat);
                $wire.set('latitude', this.selectedLat);
                $wire.set('longitude', this.selectedLng);
                this.selectLocation(parseFloat(this.selectedLat), parseFloat(this.selectedLng));
                this.map.setView([parseFloat(this.selectedLat), parseFloat(this.selectedLng)], 15);
            }
        },

        getCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        this.map.setView([lat, lng], 15);
                        this.selectLocation(lat, lng);
                    },
                    (error) => {
                        console.error('خطأ في الحصول على الموقع:', error);
                        alert('لا يمكن الحصول على موقعك الحالي. يرجى السماح بالوصول للموقع.');
                    }
                );
            } else {
                alert('المتصفح لا يدعم تحديد الموقع الجغرافي.');
            }
        },

        clearLocation() {
            this.selectedLat = null;
            this.selectedLng = null;
            
            if (this.marker) {
                this.map.removeLayer(this.marker);
                this.marker = null;
            }

            // إرسال البيانات إلى Livewire
            if (window.Livewire) {
                $wire.set('latitude', null);
                $wire.set('longitude', null);
            }
        },

        loadSavedLocation() {
            // محاولة تحميل الموقع المحفوظ
            const savedLat = document.querySelector('input[name="latitude"]')?.value;
            const savedLng = document.querySelector('input[name="longitude"]')?.value;

            if (savedLat && savedLng) {
                this.selectedLat = savedLat;
                this.selectedLng = savedLng;
                this.selectLocation(parseFloat(savedLat), parseFloat(savedLng));
                this.map.setView([parseFloat(savedLat), parseFloat(savedLng)], 15);
            }
        }
    }
}
</script>

<style>
.student-map-picker-wrapper {
    width: 100%;
    margin-bottom: 1rem;
}

.student-map-picker-wrapper .leaflet-container {
    border-radius: 8px;
}

.student-map-picker-wrapper .leaflet-popup-content-wrapper {
    direction: rtl;
    text-align: right;
}
</style>

<!-- تحميل مكتبة Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
