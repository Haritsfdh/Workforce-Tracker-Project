document.addEventListener('DOMContentLoaded', function() {
    // Initialize the map with a higher zoom level
    var map = L.map('map').setView([-6.32281873570484, 107.30675854955194], 18);

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Geofence coordinates
    var geofenceCoordinates = [
        [-6.322817239828169, 107.30665921746696],
        [-6.32281873570484, 107.30675854955194],
        [-6.322731959458808, 107.30660694151008],
        [-6.3227053005181295, 107.30669545441022],
        [-6.322694636941486, 107.30680274277401],
        [-6.322689305153076, 107.30684565811954],
        [-6.322689305153076, 107.306915395556],
        [-6.322729293564805, 107.30702804833798],
        [-6.3228039385917665, 107.30698781520158],
        [-6.322815935112948, 107.30682956486496]
    ];

    // Draw geofence polygon
    var geofence = L.polygon(geofenceCoordinates, {color: 'red', fillColor: '#f03', fillOpacity: 0.5}).addTo(map);

    // Initialize the marker
    var marker;

    // Function to perform reverse geocoding using Nominatim
    async function reverseGeocode(latitude, longitude) {
        try {
            const response = await axios.get(`https://nominatim.openstreetmap.org/reverse`, {
                params: {
                    lat: latitude,
                    lon: longitude,
                    format: 'json'
                }
            });

            const address = response.data.display_name;
            return address;
        } catch (error) {
            console.error('Error during reverse geocoding:', error);
            return null;
        }
    }

    // Function to update marker position
    async function updateMarkerPosition(latitude, longitude, user_name, job_division, status) {
        if (marker) {
            map.removeLayer(marker); // Remove existing marker
        }

        const address = await reverseGeocode(latitude, longitude);

        marker = L.marker([latitude, longitude]).addTo(map)
            .bindPopup(`<b>User:</b> ${user_name}<br><b>Job Division:</b> ${job_division}<br><b>Status:</b> ${status}<br><b>Address:</b> ${address || 'Address not found'}`)
            .openPopup();

        // Check if the marker is outside the geofence
        if (!geofence.getBounds().contains([latitude, longitude])) {
            console.log('Marker is outside the geofence!');
            var currentTime = new Date().toLocaleString(); // Get the current time as a string

            // Construct the data object to be sent to the server
            var data = {
                latitude: latitude,
                longitude: longitude,
                address: address,
                time: currentTime // Add the time to the data object
            };

            console.log('Sending data:', data);

            // Send the data to the server
            fetch('/data', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data),
            })
            .then(response => {
                if (response.ok) {
                    console.log('Data saved successfully!');
                } else {
                    console.error('Failed to save data.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    }

    // WebSocket setup
    var socket = new WebSocket('ws://192.168.1.2:3000');

    // WebSocket event handlers
    socket.onopen = function() {
        console.log('WebSocket connection established.');
    };

    socket.onmessage = function(event) {
        var gpsData = JSON.parse(event.data);
        var latitude = gpsData.latitude;
        var longitude = gpsData.longitude;
        var user_name = gpsData.user_name;
        var job_division = gpsData.job_division;
        var status = gpsData.status;
        updateMarkerPosition(latitude, longitude, user_name, job_division, status);

        // Display user biodata and division
        var userBioData = document.getElementById('user-biodata');
        userBioData.innerHTML = `<p>Nama: ${gpsData.user_name}</p><p>Divisi: ${gpsData.job_division}</p>`;
    };

    socket.onclose = function() {
        console.log('WebSocket connection closed.');
    };

    socket.onerror = function(error) {
        console.error('WebSocket error:', error);
    };

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
    setInterval(performGeolocation, 20000); // Update geolocation every 5 seconds

    const reportForm = document.getElementById('report-form');
    if (reportForm) {
        reportForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(reportForm);
            const reportPeriod = formData.get('report_period');

            // Create a hidden form to submit to the server for PDF generation
            const hiddenForm = document.createElement('form');
            hiddenForm.style.display = 'none';
            hiddenForm.method = 'post';
            hiddenForm.action = 'generate_pdf.php';

            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'report_period';
            hiddenInput.value = reportPeriod;
            hiddenForm.appendChild(hiddenInput);

            document.body.appendChild(hiddenForm);
            hiddenForm.submit();
        });
    }
});
