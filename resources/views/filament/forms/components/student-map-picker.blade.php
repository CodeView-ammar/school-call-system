<div class="map-picker-wrapper">
    <div id="map-picker-{{ $getId() }}" 
         style="height: 400px; width: 100%; border: 1px solid #e2e8f0; border-radius: 8px;"
         x-data="mapPicker()"
         x-init="initMap()"
         wire:ignore>
    </div>

    <div class="mt-2 text-sm text-gray-600">
        <p>انقر على الخريطة لتحديد موقع الطالب</p>
        <div class="grid grid-cols-2 gap-2 mt-2">
            <div>
                <strong>خط العرض:</strong> 
                <span x-text="selectedLat || 'غير محدد'"></span>
            </div>
            <div>
                <strong>خط الطول:</strong> 
                <span x-text="selectedLng || 'غير محدد'"></span>
            </div>
        </div>
    </div>
</div>

<script>
function mapPicker() {
    return {
        map: null,
        marker: null,
        selectedLat: null,
        selectedLng: null,

        initMap() {
            // إحداثيات الرياض الافتراضية
            const defaultLat = 24.7136;
            const defaultLng = 46.6753;

            // إنشاء الخريطة
            this.map = L.map('map-picker-{{ $getId() }}').setView([defaultLat, defaultLng], 11);

            // إضافة طبقة الخريطة
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(this.map);

            // إضافة حدث النقر على الخريطة
            this.map.on('click', (e) => {
                this.selectLocation(e.latlng.lat, e.latlng.lng);
            });

            // محاولة الحصول على الموقع الحالي
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    this.map.setView([lat, lng], 13);
                });
            }
        },

        selectLocation(lat, lng) {
            this.selectedLat = lat.toFixed(6);
            this.selectedLng = lng.toFixed(6);

            // إزالة العلامة السابقة
            if (this.marker) {
                this.map.removeLayer(this.marker);
            }

            // إضافة علامة جديدة
            this.marker = L.marker([lat, lng]).addTo(this.map);

            // إرسال البيانات إلى Livewire
            $wire.set('latitude', this.selectedLat);
            $wire.set('longitude', this.selectedLng);
        }
    }
}
</script>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>