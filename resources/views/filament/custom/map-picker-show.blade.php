<div class="interactive-map-container" style="background: #f8fafc; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <!-- <div class="map-header" style="padding: 16px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <h3 style="margin: 0; font-size: 18px; font-weight: 600;">خريطة المسار التفاعلية</h3>
        <p style="margin: 8px 0 0 0; font-size: 14px; opacity: 0.9;">حدد النقاط لرسم المسار تلقائياً</p>
    </div> -->

    <div class="map-controls" style="padding: 12px; background: white; border-bottom: 1px solid #e2e8f0; display: flex; gap: 12px; flex-wrap: wrap;">
        <button id="drawRoutes" type="button" class="btn-primary" style="padding: 8px 16px; background: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; transition: all 0.2s;">
            <svg width="16" height="16" style="margin-right: 6px; vertical-align: middle;" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2L13.09 8.26L22 9L13.09 9.74L12 16L10.91 9.74L2 9L10.91 8.26L12 2Z"/>
            </svg>
            رسم المسار
        </button>
        <button id="clearMap" type="button" class="btn-secondary" style="padding: 8px 16px; background: #64748b; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; transition: all 0.2s;">
            <svg width="16" height="16" style="margin-right: 6px; vertical-align: middle;" viewBox="0 0 24 24" fill="currentColor">
                <path d="M16,6L18.36,8.36L21.77,8.36L19.41,10.73L20.95,14.14L17.54,12.6L14.14,14.14L15.68,10.73L13.32,8.36L16.73,8.36L16,6M7,14A3,3 0 0,1 4,11A3,3 0 0,1 7,8A3,3 0 0,1 10,11A3,3 0 0,1 7,14M7,10A1,1 0 0,0 6,11A1,1 0 0,0 7,12A1,1 0 0,0 8,11A1,1 0 0,0 7,10Z"/>
            </svg>
            مسح الخريطة
        </button>
        <button id="showAllStops" type="button" class="btn-info" style="padding: 8px 16px; background: #06b6d4; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; transition: all 0.2s;">
            <svg width="16" height="16" style="margin-right: 6px; vertical-align: middle;" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12,11.5A2.5,2.5 0 0,1 9.5,9A2.5,2.5 0 0,1 12,6.5A2.5,2.5 0 0,1 14.5,9A2.5,2.5 0 0,1 12,11.5M12,2A7,7 0 0,0 5,9C5,14.25 12,22 12,22C12,22 19,14.25 19,9A7,7 0 0,0 12,2Z"/>
            </svg>
            عرض جميع النقاط
        </button>
        <div class="route-stats" style="margin-right: auto; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; font-size: 14px; color: #475569;">
            <span id="stopCount">0 نقطة توقف</span> | 
            <span id="routeDistance">0 كم</span>
        </div>
    </div>
<!--     
    <div class="drag-instructions" style="padding: 12px 16px; background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 100%); border-bottom: 1px solid #b3e5fc; font-size: 13px; color: #01579b;">
        <div style="display: grid; gap: 8px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                <span style="font-size: 18px;">✨</span>
                <strong style="font-size: 14px;">طرق الرسم التفاعلي للمسارات:</strong>
            </div>
            <div style="display: flex; align-items: center; gap: 8px; padding-left: 26px;">
                <span style="color: #0369a1;">👆</span>
                <span><strong>الطريقة السهلة:</strong> انقر على نقطة، ثم انقر على نقطة أخرى لرسم المسار</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px; padding-left: 26px;">
                <span style="color: #0369a1;">🖱️</span>
                <span><strong>طريقة السحب:</strong> اضغط Shift + اسحب من نقطة لأخرى</span>
            </div>
            <div style="padding-left: 26px; font-size: 12px; opacity: 0.8; margin-top: 4px;">
                سيتم إضافة النقاط تلقائياً لقائمة التوقفات مع حساب المسافة
            </div>
        </div>
    </div> -->

    <div id="map" style="height: 500px; width: 100%;" wire:ignore></div>

    <div class="map-legend" style="padding: 12px; background: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b;">
        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <div style="width: 12px; height: 12px; background: #ef4444; border-radius: 50%;"></div>
                نقطة بداية
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                <div style="width: 12px; height: 12px; background: #22c55e; border-radius: 50%;"></div>
                نقطة نهاية
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                <div style="width: 12px; height: 12px; background: #3b82f6; border-radius: 50%;"></div>
                نقاط متوسطة
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                <div style="width: 20px; height: 3px; background: linear-gradient(90deg, #8b5cf6, #06b6d4); border-radius: 2px;"></div>
                مسار الرحلة
            </div>
        </div>
    </div>
</div>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA903FiEEzDSEmogbe9-PkmA_v520gnrQ4&callback=initMap&libraries=geometry" async defer></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: 24.7136, lng: 46.6753 },
        zoom: 12,
        styles: [
            {
                featureType: 'poi',
                stylers: [{ visibility: 'simplified' }]
            },
            {
                featureType: 'transit',
                stylers: [{ visibility: 'off' }]
            }
        ],
        mapTypeControl: true,
        streetViewControl: false,
        fullscreenControl: true
    });

    var markers = [];
    var allStopsMarkers = [];
    var directionsService = new google.maps.DirectionsService();
    var directionsRenderers = [];
    var infoWindow = new google.maps.InfoWindow();
    var totalDistance = 0;
    
    // متغيرات للسحب والإفلات
    var isDragging = false;
    var dragStartPoint = null;
    var dragLine = null;
    var tempMarker = null;

    // ألوان جميلة للمسارات
    const routeColors = ['#8b5cf6', '#06b6d4', '#10b981', '#f59e0b', '#ef4444', '#ec4899'];
    let colorIndex = 0;

    function getNextColor() {
        const color = routeColors[colorIndex % routeColors.length];
        colorIndex++;
        return color;
    }

    function updateMarkers() {
        // مسح العلامات القديمة للمسار المحدد
        markers.forEach(m => m.setMap(null));
        markers = [];
        totalDistance = 0;

        // جلب الحقول من الـ Repeater
        var nameInputs = document.querySelectorAll('[data-field="name"]');
        var descriptionInputs = document.querySelectorAll('[data-field="description"]');
        var latInputs = document.querySelectorAll('[data-field="latitude"]');
        var lngInputs = document.querySelectorAll('[data-field="longitude"]');

        latInputs.forEach((latInput, index) => {
            var lngInput = lngInputs[index];
            var descriptionInput = descriptionInputs[index];
            var nameInput = nameInputs[index];

            if (!latInput || !lngInput) return;

            var lat = parseFloat(latInput.value);
            var lng = parseFloat(lngInput.value);
            var description = descriptionInput ? descriptionInput.value : '';
            var name = nameInput ? nameInput.value : '';

            if (!isNaN(lat) && !isNaN(lng)) {
                var position = { lat: lat, lng: lng };

                // تحديد لون العلامة حسب الموقع
                let markerColor = '#3b82f6'; // أزرق للنقاط المتوسطة
                let markerIcon = 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png';

                if (index === 0) {
                    markerColor = '#ef4444'; // أحمر للبداية
                    markerIcon = 'https://maps.google.com/mapfiles/ms/icons/red-dot.png';
                } else if (index === latInputs.length - 1) {
                    markerColor = '#22c55e'; // أخضر للنهاية
                    markerIcon = 'https://maps.google.com/mapfiles/ms/icons/green-dot.png';
                }

                var marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    title: name || `نقطة توقف ${index + 1}`,
                    icon: markerIcon,
                    animation: google.maps.Animation.DROP,
                    zIndex: 1000 - index
                });

                // تحسين نافذة المعلومات
                marker.addListener('click', function() {
                    var content = `
                        <p style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; max-width: 300px;color: #000000ff;">
                            <p style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px; margin: -8px -8px 12px -8px; border-radius: 8px 8px 0 0;">
                                <h4 style="margin: 0; font-size: 16px; font-weight: 600;color: #000000ff;">${name || `نقطة توقف ${index + 1}`}</h4>
                                <small style="opacity: 0.9;color: #000000ff;">
                                    ${index === 0 ? '🚩 نقطة البداية' : 
                                      index === latInputs.length - 1 ? '🏁 نقطة النهاية' : 
                                      `📍 النقطة رقم ${index + 1}`}
                                </small>
                            </p>
                            ${description ? `
                                <p style="margin-bottom: 12px;">
                                    <strong style="color: #000000ff; font-size: 14px;">الوصف:</strong>
                                    <p style="color: #000000ff; font-size: 13px; margin: 4px 0 0 0; line-height: 1.4;">${description}</p>
                                </p>
                            ` : ''}
                            <p style="display: flex; justify-content: space-between; align-items: center; padding-top: 8px; border-top: 1px solid #e5e7eb;">
                                <small style="color: #000000ff; font-size: 12px;">
                                    📍 ${lat.toFixed(4)}, ${lng.toFixed(4)}
                                </small>
                                <button onclick="centerOnMarker(${lat}, ${lng})" style="background: #3b82f6; color: white; border: none; padding: 4px 8px; border-radius: 4px; font-size: 11px; cursor: pointer;">
                                    مركز الخريطة
                                </button>
                            </p>
                        </p>
                    `;
                    infoWindow.setContent(content);
                    infoWindow.open(map, marker);
                });

                markers.push(marker);
            }
        });

        updateStats();

        // تركيز الخريطة على النقاط المحددة
        if (markers.length > 0) {
            fitMapToMarkers();
        }
    }

    function updateStats() {
        document.getElementById('stopCount').textContent = `${markers.length} نقطة توقف`;
        document.getElementById('routeDistance').textContent = `${totalDistance.toFixed(2)} كم`;
    }

    function fitMapToMarkers() {
        if (markers.length === 0) return;

        if (markers.length === 1) {
            map.setCenter(markers[0].getPosition());
            map.setZoom(15);
        } else {
            var bounds = new google.maps.LatLngBounds();
            markers.forEach(marker => bounds.extend(marker.getPosition()));
            map.fitBounds(bounds);

            // التأكد من أن التكبير ليس أكبر من اللازم
            google.maps.event.addListenerOnce(map, 'bounds_changed', function() {
                if (map.getZoom() > 16) {
                    map.setZoom(16);
                }
            });
        }
    }

    // دالة مساعدة لتمركز الخريطة على نقطة معينة
    window.centerOnMarker = function(lat, lng) {
        map.setCenter({ lat: lat, lng: lng });
        map.setZoom(16);
        infoWindow.close();
    }

    // متغيرات للنقر المزدوج
    var firstClickPoint = null;
    var clickTimeout = null;

    // إضافة وظائف السحب والإفلات
    function enableDragToDrawRoute() {
        // طريقة 1: السحب مع Shift
        map.addListener('mousedown', function(event) {
            if (event.domEvent.shiftKey) { // فقط عند الضغط مع Shift
                isDragging = true;
                dragStartPoint = event.latLng;
                
                // إنشاء علامة مؤقتة للنقطة الأولى
                tempMarker = new google.maps.Marker({
                    position: dragStartPoint,
                    map: map,
                    icon: {
                        url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
                        scaledSize: new google.maps.Size(24, 24)
                    },
                    title: 'نقطة البداية',
                    zIndex: 2000
                });

                // إنشاء خط مؤقت
                dragLine = new google.maps.Polyline({
                    path: [dragStartPoint, dragStartPoint],
                    geodesic: true,
                    strokeColor: '#ff6b6b',
                    strokeOpacity: 0.8,
                    strokeWeight: 3,
                    strokePattern: [10, 5],
                    map: map
                });

                event.stop();
            }
        });

        map.addListener('mousemove', function(event) {
            if (isDragging && dragLine) {
                // تحديث نهاية الخط أثناء الحركة
                const path = [dragStartPoint, event.latLng];
                dragLine.setPath(path);
            }
        });

        map.addListener('mouseup', function(event) {
            if (isDragging && dragStartPoint) {
                const dragEndPoint = event.latLng;
                
                // إنشاء علامة للنقطة الثانية
                const endMarker = new google.maps.Marker({
                    position: dragEndPoint,
                    map: map,
                    icon: {
                        url: 'https://maps.google.com/mapfiles/ms/icons/green-dot.png',
                        scaledSize: new google.maps.Size(24, 24)
                    },
                    title: 'نقطة النهاية',
                    zIndex: 2000
                });

                // رسم المسار الفعلي
                drawDirectionsRoute(dragStartPoint, dragEndPoint);
                
                // إضافة النقطتين إلى قائمة التوقفات
                addPointsToStopsList(dragStartPoint, dragEndPoint);
                
                // عرض رسالة نجاح
                showDragRouteSuccess(dragStartPoint, dragEndPoint);

                // تنظيف العناصر المؤقتة
                if (dragLine) {
                    dragLine.setMap(null);
                    dragLine = null;
                }
                if (tempMarker) {
                    tempMarker.setMap(null);
                    tempMarker = null;
                }

                isDragging = false;
                dragStartPoint = null;
            }
        });

        // طريقة 2: النقر المزدوج (أسهل للمستخدم)
        map.addListener('click', function(event) {
            if (firstClickPoint === null) {
                // النقرة الأولى - حفظ النقطة
                firstClickPoint = event.latLng;
                
                // إنشاء علامة مؤقتة للنقطة الأولى
                tempMarker = new google.maps.Marker({
                    position: firstClickPoint,
                    map: map,
                    icon: {
                        url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
                        scaledSize: new google.maps.Size(28, 28)
                    },
                    title: 'النقطة الأولى - انقر على نقطة ثانية',
                    animation: google.maps.Animation.BOUNCE,
                    zIndex: 2000
                });

                // عرض رسالة توجيهية
                showClickInstruction();

                // إزالة النقطة الأولى بعد 10 ثواني إذا لم يتم النقر على الثانية
                clickTimeout = setTimeout(() => {
                    if (tempMarker) {
                        tempMarker.setMap(null);
                        tempMarker = null;
                    }
                    firstClickPoint = null;
                }, 10000);
                
            } else {
                // النقرة الثانية - رسم المسار
                clearTimeout(clickTimeout);
                const secondClickPoint = event.latLng;
                
                // إنشاء علامة للنقطة الثانية
                const endMarker = new google.maps.Marker({
                    position: secondClickPoint,
                    map: map,
                    icon: {
                        url: 'https://maps.google.com/mapfiles/ms/icons/green-dot.png',
                        scaledSize: new google.maps.Size(28, 28)
                    },
                    title: 'النقطة الثانية',
                    animation: google.maps.Animation.DROP,
                    zIndex: 2000
                });

                // إيقاف أنيميشن النقطة الأولى
                if (tempMarker) {
                    tempMarker.setAnimation(null);
                }

                // رسم المسار
                drawDirectionsRoute(firstClickPoint, secondClickPoint);
                
                // إضافة النقاط لقائمة التوقفات
                addPointsToStopsList(firstClickPoint, secondClickPoint);
                
                // عرض رسالة النجاح
                showDragRouteSuccess(firstClickPoint, secondClickPoint);

                // إعادة تعيين المتغيرات
                firstClickPoint = null;
                tempMarker = null;
            }
        });
    }

    // عرض تعليمات النقر
    function showClickInstruction() {
        const instructionMsg = document.createElement('div');
        instructionMsg.style.cssText = `
            position: fixed; top: 80px; right: 20px; z-index: 10000;
            background: linear-gradient(135deg, #06b6d4, #0891b2);
            color: white; padding: 12px 20px; border-radius: 8px;
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
            font-family: 'Segoe UI', sans-serif; font-size: 14px;
            animation: slideIn 0.3s ease-out;
            max-width: 280px;
        `;
        instructionMsg.innerHTML = `
            <p style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                <span style="font-size: 18px;">👆</span>
                <strong>الخطوة التالية</strong>
            </p>
            <p style="font-size: 13px; opacity: 0.9;">
                انقر على الخريطة مرة أخرى لتحديد النقطة الثانية ورسم المسار
            </p>
        `;
        
        document.body.appendChild(instructionMsg);
        
        setTimeout(() => {
            instructionMsg.style.animation = 'slideOut 0.3s ease-in forwards';
            setTimeout(() => instructionMsg.remove(), 300);
        }, 3000);
    }

    // رسم مسار مباشر بين نقطتين
    function drawDirectionsRoute(start, end) {
        const request = {
            origin: start,
            destination: end,
            travelMode: google.maps.TravelMode.DRIVING,
            unitSystem: google.maps.UnitSystem.METRIC
        };

        directionsService.route(request, function(result, status) {
            if (status === google.maps.DirectionsStatus.OK) {
                const directionsRenderer = new google.maps.DirectionsRenderer({
                    polylineOptions: {
                        strokeColor: getNextColor(),
                        strokeOpacity: 0.9,
                        strokeWeight: 6,
                        strokePattern: []
                    },
                    map: map,
                    suppressMarkers: true,
                    preserveViewport: true
                });
                
                directionsRenderer.setDirections(result);
                directionsRenderers.push(directionsRenderer);

                // حساب المسافة
                const route = result.routes[0];
                if (route && route.legs) {
                    route.legs.forEach(leg => {
                        totalDistance += leg.distance.value / 1000;
                    });
                    updateStats();
                }
            } else {
                console.error('خطأ في رسم المسار:', status);
                
                // رسم خط مستقيم كبديل
                const polyline = new google.maps.Polyline({
                    path: [start, end],
                    geodesic: true,
                    strokeColor: '#ff6b6b',
                    strokeOpacity: 0.8,
                    strokeWeight: 4,
                    strokePattern: [10, 5],
                    map: map
                });
                directionsRenderers.push({ setMap: function(map) { polyline.setMap(map); } });
            }
        });
    }

    // إضافة النقط إلى قائمة التوقفات
    function addPointsToStopsList(startPoint, endPoint) {
        // الحصول على عنوان للنقاط
        const geocoder = new google.maps.Geocoder();
        
        Promise.all([
            getAddressFromLatLng(geocoder, startPoint),
            getAddressFromLatLng(geocoder, endPoint)
        ]).then(addresses => {
            // إضافة النقطة الأولى
            addStopToRepeater({
                name: `نقطة بداية - ${new Date().toLocaleTimeString('ar')}`,
                latitude: startPoint.lat().toFixed(6),
                longitude: startPoint.lng().toFixed(6),
                address: addresses[0],
                description: 'نقطة بداية تم إضافتها من الخريطة'
            });

            // إضافة النقطة الثانية
            addStopToRepeater({
                name: `نقطة نهاية - ${new Date().toLocaleTimeString('ar')}`,
                latitude: endPoint.lat().toFixed(6),
                longitude: endPoint.lng().toFixed(6),
                address: addresses[1],
                description: 'نقطة نهاية تم إضافتها من الخريطة'
            });

            // تحديث الخريطة
            setTimeout(() => {
                updateMarkers();
            }, 500);
        });
    }

    // الحصول على العنوان من الإحداثيات
    function getAddressFromLatLng(geocoder, latLng) {
        return new Promise((resolve) => {
            geocoder.geocode({ location: latLng }, function(results, status) {
                if (status === 'OK' && results[0]) {
                    resolve(results[0].formatted_address);
                } else {
                    resolve(`${latLng.lat().toFixed(4)}, ${latLng.lng().toFixed(4)}`);
                }
            });
        });
    }

    // إضافة نقطة توقف إلى الـ Repeater
    function addStopToRepeater(stopData) {
        // محاولة عدة طرق للعثور على زر الإضافة
        const addButton = document.querySelector('[data-action="repeater::create-item"]') || 
                         document.querySelector('button[wire\\:click*="createItem"]') ||
                         document.querySelector('button[wire\\:click*="repeater"]') ||
                         document.querySelector('.fi-btn[title*="اضف"]') ||
                         document.querySelector('button:contains("اضف محطة")') ||
                         document.querySelector('[x-on\\:click*="createItem"]');
        
        if (addButton) {
            addButton.click();
            
            // انتظار أطول وتكرار المحاولة في حالة فشل المحاولة الأولى
            setTimeout(() => {
                fillStopData(stopData, 1);
            }, 500);
            
            // محاولة ثانية في حالة فشل الأولى
            setTimeout(() => {
                fillStopData(stopData, 2);
            }, 1200);
        } else {
            // إذا لم نجد الزر، استخدم طريقة بديلة
            console.log('لم يتم العثور على زر إضافة التوقفات');
            
            // محاولة استخدام Alpine.js أو Livewire مباشرة
            if (typeof Alpine !== 'undefined' || typeof Livewire !== 'undefined') {
                setTimeout(() => {
                    tryDirectDataInsert(stopData);
                }, 100);
            }
        }
    }

    // دالة مساعدة لملء بيانات النقطة
    function fillStopData(stopData, attempt) {
        // البحث عن آخر عنصر مضاف
        const repeaterItems = document.querySelectorAll('[wire\\:key*="repeater"], [data-field="stop_id"]');
        
        if (repeaterItems.length === 0) {
            console.log(`المحاولة ${attempt}: لم يتم العثور على عناصر الـ repeater`);
            return;
        }

        // البحث عن العنصر الفارغ أو الأخير
        let targetItem = null;
        for (let i = repeaterItems.length - 1; i >= 0; i--) {
            const item = repeaterItems[i].closest('[wire\\:key*="repeater"]') || repeaterItems[i].closest('.fi-fo-repeater-item');
            if (item) {
                const nameInput = item.querySelector('[data-field="name"]');
                if (!nameInput || nameInput.value === '') {
                    targetItem = item;
                    break;
                }
            }
        }

        if (!targetItem && repeaterItems.length > 0) {
            targetItem = repeaterItems[repeaterItems.length - 1].closest('[wire\\:key*="repeater"]') || 
                        repeaterItems[repeaterItems.length - 1].closest('.fi-fo-repeater-item');
        }

        if (targetItem) {
            // ملء الحقول بالبيانات
            const fields = {
                name: targetItem.querySelector('[data-field="name"], input[id*="name"]'),
                latitude: targetItem.querySelector('[data-field="latitude"], input[id*="latitude"]'),
                longitude: targetItem.querySelector('[data-field="longitude"], input[id*="longitude"]'),
                address: targetItem.querySelector('[data-field="address"], input[id*="address"]'),
                description: targetItem.querySelector('[data-field="description"], textarea[id*="description"]')
            };

            Object.keys(fields).forEach(fieldName => {
                const field = fields[fieldName];
                if (field && stopData[fieldName]) {
                    field.value = stopData[fieldName];
                    field.dispatchEvent(new Event('input', { bubbles: true }));
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                    
                    // إشعار Alpine.js بالتغيير إذا كان متاحًا
                    if (typeof Alpine !== 'undefined') {
                        field.dispatchEvent(new CustomEvent('input'));
                    }
                }
            });

            console.log(`المحاولة ${attempt}: تم ملء البيانات بنجاح`);
        } else {
            console.log(`المحاولة ${attempt}: لم يتم العثور على العنصر المستهدف`);
        }
    }

    // طريقة بديلة لإدراج البيانات مباشرة
    function tryDirectDataInsert(stopData) {
        // إنشاء حدث مخصص لإبلاغ النموذج بالبيانات الجديدة
        const customEvent = new CustomEvent('addStopFromMap', {
            detail: stopData,
            bubbles: true
        });
        
        document.dispatchEvent(customEvent);
        
        // أو استخدام Livewire مباشرة إذا كان متاحًا
        if (typeof Livewire !== 'undefined') {
            try {
                Livewire.emit('addStopFromMap', stopData);
            } catch (e) {
                console.log('فشل في استخدام Livewire emit:', e);
            }
        }
    }

    // عرض رسالة نجاح للسحب والإفلات
    function showDragRouteSuccess(start, end) {
        const distance = google.maps.geometry.spherical.computeDistanceBetween(start, end) / 1000;
        
        const successMsg = document.createElement('div');
        successMsg.style.cssText = `
            position: fixed; top: 20px; right: 20px; z-index: 10000;
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white; padding: 16px 24px; border-radius: 12px;
            box-shadow: 0 8px 25px rgba(139, 92, 246, 0.3);
            font-family: 'Segoe UI', sans-serif; font-size: 15px;
            animation: slideIn 0.4s ease-out;
            max-width: 300px;
        `;
        successMsg.innerHTML = `
            <p style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                <p style="width: 20px; height: 20px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">✨</p>
                <strong>تم رسم المسار بنجاح!</strong>
            </p>
            <p style="font-size: 13px; opacity: 0.9;">
                المسافة: ${distance.toFixed(2)} كم<br>
                تم إضافة النقطتين لقائمة التوقفات
            </p>
        `;
        
        document.body.appendChild(successMsg);
        
        setTimeout(() => {
            successMsg.style.animation = 'slideOut 0.4s ease-in forwards';
            setTimeout(() => successMsg.remove(), 400);
        }, 4000);
    }

    function drawRoutes() {
        if (markers.length < 2) {
            alert('يحتاج المسار لنقطتين على الأقل لرسم الطريق');
            return;
        }

        // مسح المسارات القديمة
        clearRoutes();
        totalDistance = 0;

        // عرض مؤشر التحميل
        showLoadingIndicator(true);

        let routesCompleted = 0;
        const totalRoutes = markers.length - 1;

        for (let i = 0; i < markers.length - 1; i++) {
            var request = {
                origin: markers[i].getPosition(),
                destination: markers[i + 1].getPosition(),
                travelMode: google.maps.TravelMode.DRIVING,
                unitSystem: google.maps.UnitSystem.METRIC,
                avoidHighways: false,
                avoidTolls: false
            };

            directionsService.route(request, function(result, status) {
                routesCompleted++;

                if (status === google.maps.DirectionsStatus.OK) {
                    const directionsRenderer = new google.maps.DirectionsRenderer({
                        polylineOptions: {
                            strokeColor: getNextColor(),
                            strokeOpacity: 0.85,
                            strokeWeight: 5,
                            strokePattern: []
                        },
                        map: map,
                        suppressMarkers: true,
                        preserveViewport: true
                    });

                    directionsRenderer.setDirections(result);
                    directionsRenderers.push(directionsRenderer);

                    // حساب المسافة
                    const route = result.routes[0];
                    if (route && route.legs) {
                        route.legs.forEach(leg => {
                            totalDistance += leg.distance.value / 1000; // تحويل من متر إلى كيلومتر
                        });
                    }
                } else {
                    console.error('خطأ في رسم المسار:', status);

                    // في حالة فشل directions API، نرسم خط مستقيم
                    const polyline = new google.maps.Polyline({
                        path: [markers[i].getPosition(), markers[i + 1].getPosition()],
                        geodesic: true,
                        strokeColor: '#ff6b6b',
                        strokeOpacity: 0.8,
                        strokeWeight: 3,
                        strokePattern: [10, 5]
                    });
                    polyline.setMap(map);
                    directionsRenderers.push({ setMap: function(map) { polyline.setMap(map); } });
                }

                // تحديث الإحصائيات عند اكتمال جميع المسارات
                if (routesCompleted === totalRoutes) {
                    updateStats();
                    showLoadingIndicator(false);

                    if (markers.length > 1) {
                        showRouteSuccess();
                    }
                }
            });
        }
    }

    function clearRoutes() {
        directionsRenderers.forEach(dr => dr.setMap(null));
        directionsRenderers = [];
        colorIndex = 0;
    }

    function clearMap() {
        // مسح جميع العلامات والمسارات
        markers.forEach(m => m.setMap(null));
        allStopsMarkers.forEach(m => m.setMap(null));
        clearRoutes();

        markers = [];
        allStopsMarkers = [];
        totalDistance = 0;

        updateStats();
        infoWindow.close();

        // إعادة تعيين الخريطة إلى الموقع الافتراضي
        map.setCenter({ lat: 24.7136, lng: 46.6753 });
        map.setZoom(12);
    }

    function showLoadingIndicator(show) {
        const button = document.getElementById('drawRoutes');
        if (show) {
            button.innerHTML = '<p style="display: inline-block; width: 16px; height: 16px; border: 2px solid #ffffff40; border-top: 2px solid #fff; border-radius: 50%; animation: spin 1s linear infinite; margin-right: 6px;"></p>جاري الرسم...';
            button.disabled = true;
        } else {
            button.innerHTML = '<svg width="16" height="16" style="margin-right: 6px; vertical-align: middle;" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L13.09 8.26L22 9L13.09 9.74L12 16L10.91 9.74L2 9L10.91 8.26L12 2Z"/></svg>رسم المسار';
            button.disabled = false;
        }
    }

    function showRouteSuccess() {
        const successMsg = document.createElement('div');
        successMsg.style.cssText = `
            position: fixed; top: 20px; right: 20px; z-index: 10000;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white; padding: 12px 20px; border-radius: 8px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            font-family: 'Segoe UI', sans-serif; font-size: 14px;
            animation: slideIn 0.3s ease-out;
        `;
        successMsg.innerHTML = `✅ تم رسم المسار بنجاح! المسافة الإجمالية: ${totalDistance.toFixed(2)} كم`;

        document.body.appendChild(successMsg);

        setTimeout(() => {
            successMsg.style.animation = 'slideOut 0.3s ease-in forwards';
            setTimeout(() => successMsg.remove(), 300);
        }, 3000);
    }
    function showAllStops() {
        // مسح العلامات السابقة لجميع النقاط
        allStopsMarkers.forEach(m => m.setMap(null));
        allStopsMarkers = [];

        // جلب جميع نقاط التوقف من قاعدة البيانات
        showLoadingIndicator(true);

        // جلب جميع نقاط التوقف من قاعدة البيانات
        fetch('/api/stops')
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    const bounds = new google.maps.LatLngBounds();

                    data.forEach((stop, index) => {
                        const position = {
                            lat: parseFloat(stop.latitude),
                            lng: parseFloat(stop.longitude)
                        };

                        if (!isNaN(position.lat) && !isNaN(position.lng)) {
                            const stopMarker = new google.maps.Marker({
                                position: position,
                                map: map,
                                title: stop.name || `نقطة توقف ${index + 1}`,
                                icon: {
                                    url: 'https://maps.google.com/mapfiles/ms/icons/yellow-dot.png',
                                    scaledSize: new google.maps.Size(32, 32)
                                },
                                animation: google.maps.Animation.DROP,
                                zIndex: 500 + index
                            });

                            // إضافة أنيميشن bounce مؤقت
                            setTimeout(() => {
                                stopMarker.setAnimation(google.maps.Animation.BOUNCE);
                                setTimeout(() => {
                                    stopMarker.setAnimation(null);
                                }, 1200);
                            }, index * 100);

                            stopMarker.addListener('click', function() {
                                const content = `
                                    <p style="font-family: 'Segoe UI', sans-serif; max-width: 280px;">
                                        <p style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 12px; margin: -8px -8px 12px -8px; border-radius: 8px 8px 0 0;">
                                            <h4 style="margin: 0; font-size: 16px; font-weight: 600;color: #000000ff;">🚌 ${stop.name || 'نقطة توقف'}</h4>
                                            <small style="opacity: 0.9;">نقطة توقف متاحة</small>
                                        </p>
                                        ${stop.description ? `
                                            <p style="margin-bottom: 12px;">
                                                <strong style="color: #374151; font-size: 14px;">الوصف:</strong>
                                                <p style="color: #6b7280; font-size: 13px; margin: 4px 0 0 0; line-height: 1.4;">${stop.description}</p>
                                            </p>
                                        ` : ''}
                                        ${stop.address ? `
                                            <p style="margin-bottom: 12px;">
                                                <strong style="color: #374151; font-size: 14px;">العنوان:</strong>
                                                <p style="color: #6b7280; font-size: 13px; margin: 4px 0 0 0; line-height: 1.4;">${stop.address}</p>
                                            </p>
                                        ` : ''}
                                        <p style="display: flex; justify-content: space-between; align-items: center; padding-top: 8px; border-top: 1px solid #e5e7eb;">
                                            <small style="color: #9ca3af; font-size: 12px;">
                                                📍 ${position.lat.toFixed(4)}, ${position.lng.toFixed(4)}
                                            </small>
                                            <button onclick="centerOnMarker(${position.lat}, ${position.lng})" style="background: #f59e0b; color: white; border: none; padding: 4px 8px; border-radius: 4px; font-size: 11px; cursor: pointer;">
                                                مركز الخريطة
                                            </button>
                                        </p>
                                    </p>
                                `;
                                infoWindow.setContent(content);
                                infoWindow.open(map, stopMarker);
                            });

                            bounds.extend(position);
                            allStopsMarkers.push(stopMarker);
                        }
                    });

                    if (allStopsMarkers.length > 0) {
                        map.fitBounds(bounds);

                        // عرض رسالة نجاح
                        const successMsg = document.createElement('div');
                        successMsg.style.cssText = `
                            position: fixed; top: 20px; right: 20px; z-index: 10000;
                            background: linear-gradient(135deg, #f59e0b, #d97706);
                            color: white; padding: 12px 20px; border-radius: 8px;
                            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
                            font-family: 'Segoe UI', sans-serif; font-size: 14px;
                            animation: slideIn 0.3s ease-out;
                        `;
                        successMsg.innerHTML = `✅ تم عرض ${data.length} نقطة توقف متاحة`;

                        document.body.appendChild(successMsg);

                        setTimeout(() => {
                            successMsg.style.animation = 'slideOut 0.3s ease-in forwards';
                            setTimeout(() => successMsg.remove(), 300);
                        }, 3000);
                    }
                } else {
                    alert('لا توجد نقاط توقف متاحة في قاعدة البيانات');
                }

                showLoadingIndicator(false);
            })
            .catch(error => {
                console.error('خطأ في جلب نقاط التوقف:', error);
                alert('حدث خطأ في جلب نقاط التوقف من قاعدة البيانات');
                showLoadingIndicator(false);
            });
    }


    // ربط أحداث الأزرار
    document.getElementById('drawRoutes').addEventListener('click', function(e) {
        e.preventDefault();
        updateMarkers();
        drawRoutes();
    });

    document.getElementById('clearMap').addEventListener('click', function(e) {
        e.preventDefault();
        clearMap();
    });

    document.getElementById('showAllStops').addEventListener('click', function(e) {
        e.preventDefault();
        showAllStops();
    });

    // تحديث العلامات عند أي تحديث في Livewire
    if (typeof Livewire !== 'undefined') {
        Livewire.hook('element.updated', () => {
            setTimeout(() => {
                updateMarkers();
            }, 100);
        });

        // حدث مخصص من Livewire
        Livewire.on('updateMap', function(){
            updateMarkers();
            setTimeout(() => {
                if (markers.length >= 2) {
                    drawRoutes();
                }
            }, 200);
        });
    }

    // مراقبة التغييرات في الحقول
    function watchFieldChanges() {
        const observer = new MutationObserver(function(mutations) {
            let shouldUpdate = false;
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' || mutation.type === 'attributes') {
                    shouldUpdate = true;
                }
            });

            if (shouldUpdate) {
                setTimeout(updateMarkers, 300);
            }
        });

        // مراقبة التغييرات في منطقة الـ Repeater
        const repeaterContainer = document.querySelector('[wire\\:sortable]');
        if (repeaterContainer) {
            observer.observe(repeaterContainer, {
                childList: true,
                subtree: true,
                attributes: true
            });
        }
    }

    // تشغيل أولي
    setTimeout(() => {
        updateMarkers();
        watchFieldChanges();
        enableDragToDrawRoute(); // تفعيل ميزة السحب والإفلات
    }, 500);

});
</script>

<style>
/* إخفاء الحقول المطلوبة دائما */
div[wire\:key*="latitude"],
div[wire\:key*="longitude"],
div[wire\:key*="description"],
div[wire\:key*="name"] {
    display: none !important;
}

/* تحسين أزرار الخريطة */
.btn-primary:hover, .btn-secondary:hover, .btn-info:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.btn-primary:active, .btn-secondary:active, .btn-info:active {
    transform: translateY(0);
}

/* أنيميشن التحميل */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* أنيميشن الرسائل */
@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

button.gm-ui-hover-effect span{
        color: #0d0f10;
}
button.gm-ui-hover-effect{
    color: #030404;
}
/* تحسين نافذة المعلومات */
.gm-style-iw-d {
    max-width: 320px !important;
}

.gm-style-iw-c {
    border-radius: 12px !important;
    overflow: hidden !important;
}

/* تحسين شكل الخريطة */
.interactive-map-container {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* تحسين الأزرار للشاشات الصغيرة */
@media (max-width: 768px) {
    .map-controls {
        flex-direction: column;
    }

    .route-stats {
        margin-right: 0 !important;
        order: -1;
    }

    .map-legend > div {
        justify-content: center;
    }
}

/* إضافة تأثيرات للعلامات */
.marker-pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(59, 130, 246, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
    }
}
</style>
