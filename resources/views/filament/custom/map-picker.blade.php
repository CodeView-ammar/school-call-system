    <div wire:ignore>
        <div id="map" style="height: 400px; width: 100%; margin-bottom: 10px;"></div>
        <button type="button" id="current-location-btn" style="margin-bottom:10px; padding:5px 10px; background:#3B82F6; color:white; border:none; border-radius:5px;">
            تحديد موقعي الحالي
        </button>
    </div>

<script>
    document.addEventListener('livewire:load', function () {
        initMap();
    });

    function initMap() {
        const latInput = document.querySelector('input[id="data.latitude"]');
        const lngInput = document.querySelector('input[id="data.longitude"]');
        const addressInput = document.querySelector('input[id="data.address"]');
        const currentBtn = document.getElementById('current-location-btn');

        const lat = parseFloat(latInput.value) || 21.3891; // جدة افتراضي
        const lng = parseFloat(lngInput.value) || 39.8579;

        const map = new google.maps.Map(document.getElementById('map'), {
            center: { lat: lat, lng: lng },
            zoom: 13
        });

        const marker = new google.maps.Marker({
            position: { lat: lat, lng: lng },
            map: map,
            draggable: true
        });

        function updateInput(input, value) {
            input.value = value;
            input.dispatchEvent(new Event('input')); // مهم لربط Livewire
        }

        function getAddress(latLng) {
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ location: latLng }, function(results, status) {
                if (status === 'OK' && results[0]) {
                    updateInput(addressInput, results[0].formatted_address);
                }
            });
        }

        marker.addListener('dragend', function(event) {
            updateInput(latInput, event.latLng.lat().toFixed(7));
            updateInput(lngInput, event.latLng.lng().toFixed(7));
            getAddress(event.latLng);
        });

        map.addListener('click', function(event) {
            marker.setPosition(event.latLng);
            updateInput(latInput, event.latLng.lat().toFixed(7));
            updateInput(lngInput, event.latLng.lng().toFixed(7));
            getAddress(event.latLng);
        });

        // زر تحديد الموقع الحالي
        currentBtn.addEventListener('click', function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const currentLatLng = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    map.setCenter(currentLatLng);
                    marker.setPosition(currentLatLng);
                    updateInput(latInput, currentLatLng.lat.toFixed(7));
                    updateInput(lngInput, currentLatLng.lng.toFixed(7));
                    getAddress(currentLatLng);
                }, function(error) {
                    alert('تعذر الحصول على الموقع الحالي: ' + error.message);
                });
            } else {
                alert('المتصفح لا يدعم تحديد الموقع.');
            }
        });
    }
</script>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA903FiEEzDSEmogbe9-PkmA_v520gnrQ4&callback=initMap" async defer></script>
