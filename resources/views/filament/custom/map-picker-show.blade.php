<div id="map" style="height: 400px; width: 100%;" wire:ignore></div>
<button id="drawRoutes" type="button" style="margin-top: 10px;">رسم الخطط</button>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA903FiEEzDSEmogbe9-PkmA_v520gnrQ4" async defer></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: 24.7136, lng: 46.6753 },
        zoom: 6
    });

    var markers = [];
    var directionsService = new google.maps.DirectionsService();
    var directionsRenderers = [];
    var infoWindow = new google.maps.InfoWindow();

    function getRandomColor() {
        const letters = '0123456789ABCDEF';
        let color = '#';
        for (let i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }

    function updateMarkers() {
        // مسح العلامات القديمة
        markers.forEach(m => m.setMap(null));
        markers = [];
        // مسح خطوط المسار القديمة
        directionsRenderers.forEach(dr => dr.setMap(null));
        directionsRenderers = [];

        // جلب الحقول
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
                var marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    title: name || 'بدون اسم'
                });

                // عند الضغط على العلامة
                marker.addListener('click', function() {
                    var content = `
                        <p style="font-family: Arial, sans-serif;">
                            <p style="font-weight: bold; color: #007bff; font-size: 16px;">
                                ${name || 'بدون اسم'}
                            </p>
                            ${description ? `<p style="color: #555; font-size: 14px; margin-top: 4px;">${description}</p>` : ''}
                        </p>
                    `;
                    infoWindow.setContent(content);
                    infoWindow.open(map, marker);
                });

                markers.push(marker);
            }
        });

        if (markers.length > 0) {
            map.setCenter(markers[0].getPosition());
            map.setZoom(15);
        }
    }

    function drawRoutes() {
        if (markers.length < 2) return;

        for (let i = 0; i < markers.length - 1; i++) {
            var request = {
                origin: markers[i].getPosition(),
                destination: markers[i + 1].getPosition(),
                travelMode: google.maps.TravelMode.DRIVING
            };

            directionsService.route(request, function(result, status) {
                if (status === google.maps.DirectionsStatus.OK) {
                    const directionsRenderer = new google.maps.DirectionsRenderer({
                        polylineOptions: {
                            strokeColor: getRandomColor(),
                            strokeOpacity: 0.8,
                            strokeWeight: 4
                        },
                        map: map,
                        suppressMarkers: true // منع ظهور علامات A وB
                    });
                    directionsRenderer.setDirections(result);
                    directionsRenderers.push(directionsRenderer);
                } else {
                    console.error('Error fetching directions: ' + status);
                }
            });
        }
    }
    function loadStops() {
        let schoolId = "{{ Auth::user()->school_id }}";
        alert(schoolId);
        // جلب نقاط التوقف من الخادم بناءً على المدرسة المسجل بها المستخدم
        fetch('/api/stops')
            .then(response => response.json())
            .then(data => {
                // مسح العلامات القديمة
                markers.forEach(m => m.setMap(null));
                markers = [];

                data.forEach(stop => {
                    var position = { lat: parseFloat(stop.latitude), lng: parseFloat(stop.longitude) };
                    var marker = new google.maps.Marker({
                        position: position,
                        map: map,
                        title: stop.name || 'بدون اسم'
                    });

                    // إضافة حدث النقر على العلامة
                    marker.addListener('click', function() {
                        var content = `
                            <p style="font-family: Arial, sans-serif;">
                                <strong style="color: #007bff;">${stop.name || 'بدون اسم'}</strong>
                                <p style="color: #555;">${stop.description || ''}</p>
                            </p>
                        `;
                        infoWindow.setContent(content);
                        infoWindow.open(map, marker);
                    });

                    markers.push(marker);
                });
                
                if (markers.length > 0) {
                    map.setCenter(markers[0].getPosition());
                    map.setZoom(15);
                }
            })
            .catch(error => console.error('Error fetching stops:', error));
    }


    // تحديث العلامات عند أي تحديث Livewire
    Livewire.hook('element.updated', () => {
        updateMarkers();
    });

    // حدث مخصص من Livewire
    Livewire.on('updateMap', function(){
        updateMarkers();
        drawRoutes();
    });

    // عند الضغط على زر رسم الخطوط
    document.getElementById('drawRoutes').addEventListener('click', function(e) {
        e.preventDefault();
        updateMarkers();
        drawRoutes();
    });

    function loadStops() {
    // جلب نقاط التوقف من الخادم
    fetch('/api/stops')
        .then(response => response.json())
        .then(data => {
            data.forEach(stop => {
                var position = { lat: parseFloat(stop.latitude), lng: parseFloat(stop.longitude) };
                var marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    title: stop.name || 'بدون اسم'
                });

                // إضافة حدث النقر على العلامة
                marker.addListener('click', function() {
                    var content = `
                        <p style="font-family: Arial, sans-serif;">
                            <strong style="color: #007bff;">${stop.name || 'بدون اسم'}</strong>
                            <p style="color: #555;">${stop.description || ''}</p>
                        </p>
                    `;
                    infoWindow.setContent(content);
                    infoWindow.open(map, marker);
                });

                markers.push(marker);
            });
            
            if (markers.length > 0) {
                map.setCenter(markers[0].getPosition());
                map.setZoom(15);
            }
        })
        .catch(error => console.error('Error fetching stops:', error));
    }
    // أول تشغيل
    updateMarkers();
    loadStops();

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
</style>
