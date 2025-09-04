<div class="student-map-picker-wrapper">
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">موقع الطالب على الخريطة</label>
        <p class="text-sm text-gray-500 mb-4">انقر على الخريطة لتحديد موقع سكن الطالب</p>
    </div>

    <div id="student-map-picker-{{ $getId() ?? 'default' }}"
         style="height: 400px; width: 100%; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 16px;"
         x-data="studentMapPicker('{{ $getId() ?? 'default' }}')"
         x-init="initMap()"
         wire:ignore>
    </div>

    <input type="hidden" 
           name="latitude" 
           id="latitude-{{ $getId() ?? 'default' }}"
           wire:model.defer="latitude" />
    <input type="hidden" 
           name="longitude" 
           id="longitude-{{ $getId() ?? 'default' }}"
           wire:model.defer="longitude" />

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

    <!-- عرض الإحداثيات الحالية -->
    <div class="mt-2 text-sm text-gray-600" x-show="selectedLat && selectedLng">
        <span>خط العرض: </span><span x-text="selectedLat"></span> | 
        <span>خط الطول: </span><span x-text="selectedLng"></span>
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
            // التأكد من تحميل Leaflet قبل تهيئة الخريطة
            if (typeof L === 'undefined') {
                setTimeout(() => this.initMap(), 100);
                return;
            }

            const mapContainer = `student-map-picker-${this.componentId}`;

            // التأكد من وجود العنصر قبل إنشاء الخريطة
            if (!document.getElementById(mapContainer)) {
                setTimeout(() => this.initMap(), 100);
                return;
            }

            // إنشاء الخريطة
            this.map = L.map(mapContainer).setView([24.7136, 46.6753], 11);

            // إضافة طبقة الخريطة
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(this.map);

            // إضافة حدث النقر على الخريطة
            this.map.on('click', (e) => {
                this.selectLocation(e.latlng.lat, e.latlng.lng);
            });

            // تحميل الموقع المحفوظ إن وجد
            this.loadSavedLocation();
        },

        selectLocation(lat, lng) {
            this.selectedLat = lat.toFixed(6);
            this.selectedLng = lng.toFixed(6);

            // إزالة العلامة السابقة إن وجدت
            if (this.marker) {
                this.map.removeLayer(this.marker);
            }

            // إضافة علامة جديدة
            this.marker = L.marker([lat, lng]).addTo(this.map);

            // تحديث الحقول المخفية
            const latInput = document.getElementById(`latitude-${this.componentId}`);
            const lngInput = document.getElementById(`longitude-${this.componentId}`);

            if (latInput) {
                latInput.value = this.selectedLat;
                latInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
            if (lngInput) {
                lngInput.value = this.selectedLng;
                lngInput.dispatchEvent(new Event('input', { bubbles: true }));
            }

            // تحديث Livewire إذا كان متاحاً
            if (window.Livewire && this.$wire) {
                this.$wire.set('latitude', parseFloat(this.selectedLat));
                this.$wire.set('longitude', parseFloat(this.selectedLng));
            }
        },

        getCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        this.selectLocation(lat, lng);
                        this.map.setView([lat, lng], 15);
                    },
                    (error) => {
                        console.error('خطأ في الحصول على الموقع:', error);
                        alert('لا يمكن الحصول على موقعك الحالي. يرجى السماح بالوصول للموقع أو تحديد الموقع يدوياً.');
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 60000
                    }
                );
            } else {
                alert('المتصفح لا يدعم تحديد الموقع الجغرافي.');
            }
        },

        clearLocation() {
            this.selectedLat = null;
            this.selectedLng = null;

            // إزالة العلامة من الخريطة
            if (this.marker) {
                this.map.removeLayer(this.marker);
                this.marker = null;
            }

            // مسح حقول النموذج
            const latInput = document.getElementById(`latitude-${this.componentId}`);
            const lngInput = document.getElementById(`longitude-${this.componentId}`);

            if (latInput) {
                latInput.value = '';
                latInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
            if (lngInput) {
                lngInput.value = '';
                lngInput.dispatchEvent(new Event('input', { bubbles: true }));
            }

            // تحديث Livewire
            if (window.Livewire && this.$wire) {
                this.$wire.set('latitude', null);
                this.$wire.set('longitude', null);
            }
        },

        loadSavedLocation() {
            // محاولة تحميل البيانات من عدة مصادر
            let savedLat = null;
            let savedLng = null;

            // البحث في الحقول المخفية
            const latField = document.getElementById(`latitude-${this.componentId}`);
            const lngField = document.getElementById(`longitude-${this.componentId}`);

            if (latField && latField.value) {
                savedLat = latField.value;
            }

            if (lngField && lngField.value) {
                savedLng = lngField.value;
            }

            // البحث في حقول أخرى محتملة
            if (!savedLat || !savedLng) {
                const altLatField = document.querySelector('input[name="latitude"]');
                const altLngField = document.querySelector('input[name="longitude"]');

                if (altLatField && altLatField.value) {
                    savedLat = altLatField.value;
                }

                if (altLngField && altLngField.value) {
                    savedLng = altLngField.value;
                }
            }

            // تطبيق الموقع المحفوظ إن وجد
            if (savedLat && savedLng && savedLat !== '' && savedLng !== '') {
                const lat = parseFloat(savedLat);
                const lng = parseFloat(savedLng);

                if (!isNaN(lat) && !isNaN(lng)) {
                    this.selectedLat = lat.toFixed(6);
                    this.selectedLng = lng.toFixed(6);
                    this.selectLocation(lat, lng);
                    this.map.setView([lat, lng], 15);
                }
            }
        }
    }
}

// التأكد من تحميل Leaflet قبل تشغيل الكود
document.addEventListener('DOMContentLoaded', function() {
    if (typeof L === 'undefined') {
        console.warn('Leaflet لم يتم تحميله بعد');
    }
});
</script>

<style>
.student-map-picker-wrapper .leaflet-container {
    border-radius: 8px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.student-map-picker-wrapper .leaflet-popup-content-wrapper {
    direction: rtl;
    text-align: right;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.student-map-picker-wrapper .leaflet-popup-content {
    margin: 8px 12px;
    font-size: 14px;
}

.student-map-picker-wrapper .leaflet-control-attribution {
    font-size: 10px;
}

/* تحسينات إضافية للواجهة */
.student-map-picker-wrapper button:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

.student-map-picker-wrapper button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>

<!-- تحميل مكتبة Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
      crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>