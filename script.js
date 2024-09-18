document.addEventListener('DOMContentLoaded', function() {
    // Initialize the map with a higher zoom level
    var map = L.map('map').setView([-6.304849, 107.314286], 18);

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Geofence coordinates
    var geofenceCoordinates = [
        [-6.304849196732984, 107.31428582615027],
        [-6.304846700444834, 107.3143670306176],
        [-6.304846700444834, 107.31444823508492],
        [-6.304914100220505, 107.31445074656328],
        [-6.304983164179073, 107.31445493236059],
        [-6.305026433160483, 107.31443651485252],
        [-6.3050305936392785, 107.31435614754464],
        [-6.305031425735027, 107.31429587206375],
        [-6.304992317233138, 107.31428666330972],
        [-6.304913268124565, 107.31428750046916]
    ];

    // Draw geofence polygon
    var geofence = L.polygon(geofenceCoordinates, {color: 'red', fillColor: '#f03', fillOpacity: 0.5}).addTo(map);

    // Initialize the marker
    var marker;

    // Function to perform geolocation
    function performGeolocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var lat = position.coords.latitude;
                var lon = position.coords.longitude;
                var gpsStatus = pointInPolygon(lat, lon, geofenceCoordinates) ? 'INSIDE' : 'OUTSIDE';
                
                // Update the marker position or create a new marker if it doesn't exist
                if (marker) {
                    marker.setLatLng([lat, lon]).update();
                } else {
                    marker = L.marker([lat, lon]).addTo(map);
                }

                // Center the map on the marker
                map.setView([lat, lon]);

                console.log('Latitude: ' + lat + ', Longitude: ' + lon + ', Status: ' + gpsStatus);

                // Send GPS data to server
                axios.post('update_gps.php', {
                    latitude: lat,
                    longitude: lon,
                    status: gpsStatus
                }).then(function(response) {
                    console.log('GPS data sent successfully:', response.data);
                }).catch(function(error) {
                    console.error('Error sending GPS data:', error);
                });
            }, function(error) {
                console.error('Error getting location:', error.message);
            });
        } else {
            console.error('Geolocation is not supported by this browser.');
        }
    }

    // Function to check if a point is inside a polygon
    function pointInPolygon(lat, lon, polygon) {
        var inside = false;
        for (var i = 0, j = polygon.length - 1; i < polygon.length; j = i++) {
            var xi = polygon[i][0], yi = polygon[i][1];
            var xj = polygon[j][0], yj = polygon[j][1];

            var intersect = ((yi > lon) != (yj > lon)) && (lat < (xj - xi) * (lon - yi) / (yj - yi) + xi);
            if (intersect) inside = !inside;
        }
        return inside;
    }

    // Toggle views
    const mapViewButton = document.getElementById('map-view');
    const objectDetectionViewButton = document.getElementById('object-detection-view');
    const mapView = document.getElementById('map');
    const objectDetectionView = document.getElementById('object-detection-results');

    mapViewButton.addEventListener('click', () => {
        mapView.classList.remove('hidden');
        objectDetectionView.classList.add('hidden');
        mapViewButton.classList.add('active');
        objectDetectionViewButton.classList.remove('active');
    });

    objectDetectionViewButton.addEventListener('click', () => {
        mapView.classList.add('hidden');
        objectDetectionView.classList.remove('hidden');
        mapViewButton.classList.remove('active');
        objectDetectionViewButton.classList.add('active');
    });

    // Handle sidebar toggle
    const hamburgerMenu = document.getElementById('hamburger-menu');
    const sidebar = document.getElementById('sidebar');

    hamburgerMenu.addEventListener('click', () => {
        sidebar.classList.toggle('open');
    });

    // Perform geolocation periodically
    setInterval(performGeolocation, 30000);
});
