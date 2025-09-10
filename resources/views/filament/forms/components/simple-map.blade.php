<div>
    @once
        @push('styles')
        <style>
            .google-map-container {
                font-family: inherit;
            }
            .search-results {
                max-height: 200px;
                overflow-y: auto;
                border: 1px solid #e5e7eb;
                border-top: none;
                background: white;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            }
            .search-result-item {
                padding: 10px;
                cursor: pointer;
                border-bottom: 1px solid #f3f4f6;
            }
            .search-result-item:hover {
                background-color: #f9fafb;
            }
            .search-result-item:last-child {
                border-bottom: none;
            }
        </style>
        @endpush
    @endonce

    <div class="space-y-4">
        <!-- معلومات الموقع -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 rounded-lg">
               
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
        <div class="relative">
            <div class="flex gap-2">
                <div class="flex-1 relative">
                    <input 
                        type="text" 
                        id="map-search-input"
                        placeholder="ابحث عن موقع (مثال: الرياض، حي النرجس)"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                        autocomplete="off"
                    >
                    <div id="search-results" class="absolute w-full z-50 hidden search-results"></div>
                </div>
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
        </div>

        <!-- الخريطة -->
        <div 
            id="branch-location-map"
            style="height: 400px"
            class="w-full border border-gray-300 rounded-lg google-map-container"
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

    @push('scripts')


<!-- Google Maps API Script -->
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&libraries=places&callback=initBranchMap" async defer></script>

<script>
    let branchMap, branchMarker, placesService, geocoder;
    let searchTimeout;

    function initBranchMap() {
        const mapContainer = document.getElementById('branch-location-map');
        if (!mapContainer || branchMap) return;

        // إنشاء الخريطة بـ Google Maps
        branchMap = new google.maps.Map(mapContainer, {
            center: { lat: 24.7136, lng: 46.6753 },
            zoom: 13,
            mapTypeControl: true,
            streetViewControl: true,
            fullscreenControl: true,
            zoomControl: true,
            mapTypeControlOptions: {
                mapTypeIds: ['roadmap', 'satellite', 'hybrid', 'terrain']
            }
        });

        // إنشاء العلامة
        branchMarker = new google.maps.Marker({
            position: { lat: 24.7136, lng: 46.6753 },
            map: branchMap,
            draggable: true,
            title: 'موقع الفرع',
            animation: google.maps.Animation.DROP
        });

        // إنشاء خدمات Google
        placesService = new google.maps.places.PlacesService(branchMap);
        geocoder = new google.maps.Geocoder();

        // أحداث الخريطة
        branchMap.addListener('click', function(event) {
            updateBranchMarkerPosition(event.latLng.lat(), event.latLng.lng());
        });

        branchMarker.addListener('dragend', function(event) {
            updateBranchMarkerPosition(event.latLng.lat(), event.latLng.lng());
        });

        // إعداد البحث المتقدم
        setupAdvancedSearch();

        // جلب العنوان الأولي
        reverseGeocodeBranch(24.7136, 46.6753);
    }

    function setupAdvancedSearch() {
        const searchInput = document.getElementById('map-search-input');
        const resultsContainer = document.getElementById('search-results');
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length < 3) {
                resultsContainer.innerHTML = '';
                resultsContainer.classList.add('hidden');
                return;
            }
            
            searchTimeout = setTimeout(() => {
                performAdvancedSearch(query, resultsContainer);
            }, 300);
        });
        
        // إخفاء النتائج عند النقر خارج المربع
        document.addEventListener('click', function(event) {
            if (!searchInput.contains(event.target) && !resultsContainer.contains(event.target)) {
                resultsContainer.classList.add('hidden');
            }
        });
    }

    function performAdvancedSearch(query, resultsContainer) {
        const request = {
            query: query,
            fields: ['name', 'formatted_address', 'geometry', 'place_id'],
            locationBias: branchMap.getCenter()
        };
        
        placesService.textSearch(request, function(results, status) {
            resultsContainer.innerHTML = '';
            
            if (status === google.maps.places.PlacesServiceStatus.OK && results) {
                results.slice(0, 5).forEach(function(place) {
                    const resultItem = document.createElement('div');
                    resultItem.className = 'search-result-item';
                    resultItem.innerHTML = `
                        <div class="font-medium text-gray-900">${place.name || 'غير معروف'}</div>
                        <div class="text-sm text-gray-600">${place.formatted_address || ''}</div>
                    `;
                    
                    resultItem.addEventListener('click', function() {
                        if (place.geometry && place.geometry.location) {
                            const lat = place.geometry.location.lat();
                            const lng = place.geometry.location.lng();
                            updateBranchMarkerPosition(lat, lng);
                            branchMap.setZoom(16);
                            document.getElementById('map-search-input').value = place.formatted_address || place.name;
                            resultsContainer.classList.add('hidden');
                        }
                    });
                    
                    resultsContainer.appendChild(resultItem);
                });
                
                resultsContainer.classList.remove('hidden');
            } else {
                resultsContainer.classList.add('hidden');
            }
        });
    }

    function updateBranchMarkerPosition(lat, lng) {
        branchMarker.setPosition({ lat: lat, lng: lng });
        branchMap.setCenter({ lat: lat, lng: lng });
        
        // تحديث العرض
        // document.getElementById('map-latitude-display').value = lat.toFixed(6);
        // document.getElementById('map-longitude-display').value = lng.toFixed(6);
        document.getElementById('data.latitude').value = lat.toFixed(6);
        document.getElementById('data.longitude').value = lng.toFixed(6);

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
        const latLng = { lat: lat, lng: lng };
        
        geocoder.geocode({ location: latLng }, function(results, status) {
            if (status === 'OK' && results[0]) {
                const address = results[0].formatted_address;
                document.getElementById('map-address-display').value = address;
                
                // إضافة معلومات إضافية عن المكان
                const addressComponents = results[0].address_components;
                let cityName = '';
                let countryName = '';
                
                addressComponents.forEach(component => {
                    if (component.types.includes('locality') || component.types.includes('administrative_area_level_1')) {
                        cityName = component.long_name;
                    }
                    if (component.types.includes('country')) {
                        countryName = component.long_name;
                    }
                });
                
                // يمكن إضافة المزيد من المعلومات حسب الحاجة
            } else {
                document.getElementById('map-address-display').value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
            }
        });
    }

    function searchMapLocation() {
        const query = document.getElementById('map-search-input').value.trim();
        if (!query) return;

        const request = {
            query: query,
            fields: ['name', 'formatted_address', 'geometry'],
            locationBias: branchMap.getCenter()
        };
        
        placesService.textSearch(request, function(results, status) {
            if (status === google.maps.places.PlacesServiceStatus.OK && results && results[0]) {
                const place = results[0];
                if (place.geometry && place.geometry.location) {
                    const lat = place.geometry.location.lat();
                    const lng = place.geometry.location.lng();
                    updateBranchMarkerPosition(lat, lng);
                    branchMap.setZoom(16);
                    
                    // تحديث مربع البحث
                    document.getElementById('map-search-input').value = place.formatted_address || place.name;
                }
            } else {
                alert('لم يتم العثور على الموقع. يرجى المحاولة مرة أخرى.');
            }
        });
    }

    function getCurrentMapLocation() {
        if (navigator.geolocation) {
            // إضافة مؤشر التحميل
            const button = document.querySelector('[onclick="getCurrentMapLocation()"]');
            const originalText = button.innerHTML;
            button.innerHTML = 'جاري التحديد...';
            button.disabled = true;
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    updateBranchMarkerPosition(lat, lng);
                    branchMap.setZoom(16);
                    
                    // إعادة تعيين الزر
                    button.innerHTML = originalText;
                    button.disabled = false;
                },
                function(error) {
                    let errorMessage = 'لا يمكن تحديد موقعك الحالي.';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage = 'تم رفض الوصول للموقع. يرجى السماح للموقع بالوصول لموقعك.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage = 'معلومات الموقع غير متاحة.';
                            break;
                        case error.TIMEOUT:
                            errorMessage = 'انتهت مهلة تحديد الموقع.';
                            break;
                    }
                    alert(errorMessage);
                    
                    // إعادة تعيين الزر
                    button.innerHTML = originalText;
                    button.disabled = false;
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 600000
                }
            );
        } else {
            alert('متصفحك لا يدعم تحديد الموقع.');
        }
    }

</script>
    @endpush
