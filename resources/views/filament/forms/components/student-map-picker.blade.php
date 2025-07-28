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

    <input type="hidden" wire:model="latitude" />
    <input type="hidden" wire:model="longitude" />

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
            const mapContainer = `student-map-picker-${this.componentId}`;
            this.map = L.map(mapContainer).setView([24.7136, 46.6753], 11);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(this.map);

            this.map.on('click', (e) => {
                this.selectLocation(e.latlng.lat, e.latlng.lng);
            });

            this.loadSavedLocation();
        },

        selectLocation(lat, lng) {
            this.selectedLat = lat.toFixed(6);
            this.selectedLng = lng.toFixed(6);

            if (this.marker) {
                this.map.removeLayer(this.marker);
            }

            this.marker = L.marker([lat, lng])
                .addTo(this.map)
                .bindPopup('موقع الطالب')
                .openPopup();

            if (window.Livewire) {
                this.$wire.set('latitude', lat);
                this.$wire.set('longitude', lng);
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
                    () => alert('لا يمكن الحصول على موقعك الحالي. يرجى السماح بالوصول للموقع.')
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

            if (window.Livewire) {
                this.$wire.set('latitude', null);
                this.$wire.set('longitude', null);
            }
        },

        loadSavedLocation() {
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
.student-map-picker-wrapper .leaflet-container {
    border-radius: 8px;
}
.student-map-picker-wrapper .leaflet-popup-content-wrapper {
    direction: rtl;
    text-align: right;
}
</style>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>