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

    <div
        x-data="mapPicker({
            statePath: '{{ $getStatePath() }}',
            latitudeField: '{{ $getLatitudeField() }}',
            longitudeField: '{{ $getLongitudeField() }}',
            defaultLat: {{ $getDefaultLat() }},
            defaultLng: {{ $getDefaultLng() }},
            zoom: {{ $getZoom() }},
            height: '{{ $getHeight() }}'
        })"
        wire:ignore
        {{ $attributes->merge($getExtraAttributes()) }}
    >
        <div class="space-y-4">
            <!-- معلومات الموقع -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 rounded-lg">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">خط العرض (Latitude)</label>
                    <input 
                        type="text" 
                        x-model="latitude" 
                        readonly
                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-sm"
                        placeholder="سيتم تحديده من الخريطة"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">خط الطول (Longitude)</label>
                    <input 
                        type="text" 
                        x-model="longitude" 
                        readonly
                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-sm"
                        placeholder="سيتم تحديده من الخريطة"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">العنوان</label>
                    <input 
                        type="text" 
                        x-model="address" 
                        readonly
                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-sm"
                        placeholder="سيتم جلبه من الخريطة"
                    >
                </div>
            </div>

            <!-- زر البحث -->
            <div class="flex gap-2">
                <input 
                    type="text" 
                    x-model="searchQuery"
                    @keydown.enter.prevent="searchLocation()"
                    placeholder="ابحث عن موقع (مثال: الرياض، حي النرجس)"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm"
                >
                <button 
                    type="button"
                    @click="searchLocation()"
                    class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 text-sm"
                >
                    بحث
                </button>
                <button 
                    type="button"
                    @click="getCurrentLocation()"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm"
                >
                    موقعي الحالي
                </button>
            </div>

            <!-- الخريطة -->
            <div 
                x-ref="mapContainer"
                :style="`height: ${height}`"
                class="w-full border border-gray-300 rounded-lg"
            ></div>

            <!-- تعليمات -->
            <div class="text-sm text-gray-600 bg-blue-50 p-3 rounded-lg">
                <strong>كيفية الاستخدام:</strong>
                <ul class="mt-1 list-disc list-inside space-y-1">
                    <li>انقر على الخريطة لتحديد موقع المدرسة</li>
                    <li>استخدم صندوق البحث للعثور على موقع محدد</li>
                    <li>انقر على "موقعي الحالي" لاستخدام موقعك الحالي</li>
                    <li>يمكنك سحب العلامة لتعديل الموقع</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
    function mapPicker(config) {
        return {
            map: null,
            marker: null,
            latitude: config.defaultLat,
            longitude: config.defaultLng,
            address: '',
            searchQuery: '',
            height: config.height,
            
            init() {
                this.$nextTick(() => {
                    this.initMap();
                    this.updateFormFields();
                });
            },

            initMap() {
                // إنشاء الخريطة باستخدام Leaflet
                this.map = L.map(this.$refs.mapContainer).setView([this.latitude, this.longitude], config.zoom);

                // إضافة طبقة الخريطة من OpenStreetMap
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 19
                }).addTo(this.map);

                // إضافة العلامة الأولية
                this.marker = L.marker([this.latitude, this.longitude], {
                    draggable: true,
                    title: 'موقع المدرسة'
                }).addTo(this.map);

                // حدث النقر على الخريطة
                this.map.on('click', (e) => {
                    this.updateMarkerPosition(e.latlng.lat, e.latlng.lng);
                });

                // حدث سحب العلامة
                this.marker.on('dragend', (e) => {
                    const position = e.target.getLatLng();
                    this.updateMarkerPosition(position.lat, position.lng);
                });

                // جلب العنوان الأولي
                this.reverseGeocode(this.latitude, this.longitude);
            },

            updateMarkerPosition(lat, lng) {
                this.latitude = lat;
                this.longitude = lng;
                
                this.marker.setLatLng([lat, lng]);
                this.map.setView([lat, lng]);
                
                this.reverseGeocode(lat, lng);
                this.updateFormFields();
            },

            reverseGeocode(lat, lng) {
                // استخدام خدمة Nominatim للبحث العكسي
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=ar`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.display_name) {
                            this.address = data.display_name;
                        } else {
                            this.address = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                        }
                    })
                    .catch(() => {
                        this.address = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                    });
            },

            searchLocation() {
                if (!this.searchQuery.trim()) return;

                // البحث عن الموقع باستخدام Nominatim
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.searchQuery)}&accept-language=ar&limit=1`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.length > 0) {
                            const result = data[0];
                            const lat = parseFloat(result.lat);
                            const lng = parseFloat(result.lon);
                            this.updateMarkerPosition(lat, lng);
                            this.map.setZoom(15);
                        } else {
                            alert('لم يتم العثور على الموقع. يرجى المحاولة مرة أخرى.');
                        }
                    })
                    .catch(() => {
                        alert('حدث خطأ أثناء البحث. يرجى المحاولة مرة أخرى.');
                    });
            },

            getCurrentLocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            this.updateMarkerPosition(lat, lng);
                            this.map.setZoom(16);
                        },
                        (error) => {
                            alert('لا يمكن تحديد موقعك الحالي.');
                        }
                    );
                } else {
                    alert('متصفحك لا يدعم تحديد الموقع.');
                }
            },

            updateFormFields() {
                // تحديث الحقول في النموذج مباشرة
                const latInput = document.querySelector(`input[wire\\:model="${config.latitudeField}"], input[name="${config.latitudeField}"]`);
                const lngInput = document.querySelector(`input[wire\\:model="${config.longitudeField}"], input[name="${config.longitudeField}"]`);
                
                if (latInput) {
                    latInput.value = this.latitude;
                    latInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
                
                if (lngInput) {
                    lngInput.value = this.longitude;
                    lngInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }
        }
    }
    </script>
</div>