
<div class="student-map-picker-wrapper">
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">موقع الطالب على الخريطة</label>
        <p class="text-sm text-gray-500 mb-4">انقر على الخريطة لتحديد موقع سكن الطالب</p>
    </div>

    <div id="student-map-picker-{{ $getId() ?? 'default' }}"
         style="height: 400px; width: 100%; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 16px;"
         x-data="studentGoogleMapPicker('{{ $getId() ?? 'default' }}')"
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

    <!-- عرض الإحداثيات والعنوان -->
    <div class="mt-2 text-sm text-gray-600" x-show="selectedLat && selectedLng">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 p-3 bg-gray-50 rounded-lg">
            <div>
                <span class="font-medium">خط العرض: </span>
                <span x-text="selectedLat"></span>
            </div>
            <div>
                <span class="font-medium">خط الطول: </span>
                <span x-text="selectedLng"></span>
            </div>
            <div class="md:col-span-2" x-show="selectedAddress">
                <span class="font-medium">العنوان: </span>
                <span x-text="selectedAddress"></span>
            </div>
        </div>
    </div>
</div>

<script>
function studentGoogleMapPicker(componentId) {
    return {
        map: null,
        marker: null,
        geocoder: null,
        selectedLat: null,
        selectedLng: null,
        selectedAddress: '',
        componentId: componentId,

        initMap() {
            // التأكد من تحميل Google Maps قبل تهيئة الخريطة
            if (typeof google === 'undefined' || !google.maps) {
                // تحميل Google Maps API
                this.loadGoogleMaps();
                return;
            }

            this.createMap();
        },

        loadGoogleMaps() {
            // استخدم مفتاح API من متغير البيئة
            const apiKey = '{{ config("services.google_maps.api_key") }}';
            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=geometry,places&callback=initGoogleMapsCallback`;
            script.async = true;
            script.defer = true;
            
            // إنشاء callback عالمي
            window.initGoogleMapsCallback = () => {
                this.createMap();
            };
            
            document.head.appendChild(script);
        },

        createMap() {
            const mapContainer = document.getElementById(`student-map-picker-${this.componentId}`);
            
            if (!mapContainer) {
                setTimeout(() => this.createMap(), 100);
                return;
            }

            // إنشاء الخريطة
            const defaultLocation = { lat: 24.7136, lng: 46.6753 }; // الرياض
            
            this.map = new google.maps.Map(mapContainer, {
                zoom: 11,
                center: defaultLocation,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                gestureHandling: 'cooperative',
                zoomControl: true,
                streetViewControl: false,
                fullscreenControl: false
            });

            // إنشاء Geocoder للحصول على العناوين
            this.geocoder = new google.maps.Geocoder();

            // إضافة حدث النقر على الخريطة
            this.map.addListener('click', (event) => {
                const lat = event.latLng.lat();
                const lng = event.latLng.lng();
                this.selectLocation(lat, lng);
                this.getAddressFromLatLng(lat, lng);
            });

            // تحميل الموقع المحفوظ إن وجد
            this.loadSavedLocation();
        },

        selectLocation(lat, lng) {
            this.selectedLat = lat.toFixed(6);
            this.selectedLng = lng.toFixed(6);

            // إزالة العلامة السابقة إن وجدت
            if (this.marker) {
                this.marker.setMap(null);
            }

            // إضافة علامة جديدة
            this.marker = new google.maps.Marker({
                position: { lat: lat, lng: lng },
                map: this.map,
                draggable: true,
                title: 'موقع الطالب'
            });

            // إضافة حدث السحب للعلامة
            this.marker.addListener('dragend', (event) => {
                const newLat = event.latLng.lat();
                const newLng = event.latLng.lng();
                this.selectedLat = newLat.toFixed(6);
                this.selectedLng = newLng.toFixed(6);
                this.updateFormFields();
                this.getAddressFromLatLng(newLat, newLng);
            });

            // تحديث الحقول المخفية
            this.updateFormFields();
        },

        updateFormFields() {
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

            // تحديث Livewire
            if (window.Livewire && this.$wire) {
                this.$wire.set('latitude', parseFloat(this.selectedLat));
                this.$wire.set('longitude', parseFloat(this.selectedLng));
            }
        },

        getAddressFromLatLng(lat, lng) {
            if (!this.geocoder) return;

            const latLng = { lat: lat, lng: lng };
            
            this.geocoder.geocode({ location: latLng }, (results, status) => {
                if (status === 'OK' && results[0]) {
                    this.selectedAddress = results[0].formatted_address;
                } else {
                    this.selectedAddress = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                }
            });
        },

        getCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        this.selectLocation(lat, lng);
                        this.map.setCenter({ lat: lat, lng: lng });
                        this.map.setZoom(15);
                        this.getAddressFromLatLng(lat, lng);
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
            this.selectedAddress = '';

            // إزالة العلامة من الخريطة
            if (this.marker) {
                this.marker.setMap(null);
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
                    this.selectLocation(lat, lng);
                    this.map.setCenter({ lat: lat, lng: lng });
                    this.map.setZoom(15);
                    this.getAddressFromLatLng(lat, lng);
                }
            }
        }
    }
}
</script>

<style>
.student-map-picker-wrapper {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.student-map-picker-wrapper button:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

.student-map-picker-wrapper button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* تحسينات للخريطة */
.student-map-picker-wrapper .gm-style {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.student-map-picker-wrapper .gm-style .gm-style-iw {
    direction: rtl;
    text-align: right;
}
</style>
