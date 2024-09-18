<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map and GPS Tracking</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
    <style>
        :root {
            --primary: rgb(5, 135, 243);
            --bg: #010101;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
            background-color: var(--bg);
            color: white;
        }
        .navbar {
            background-color: #333;
            color: white;
            padding: 20px;
            box-sizing: border-box;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }
        .navbar .menu-items {
            display: flex;
            align-items: center;
        }
        .navbar .menu-item {
            margin-right: 20px;
            cursor: pointer;
        }
        .navbar .menu-item.active {
            color: var(--primary);
            border-bottom: 2px solid var(--primary);
        }
        .hamburger-menu {
            display: block;
            cursor: pointer;
        }
        .sidebar {
            position: fixed;
            right: -200px; /* Off-screen initially */
            top: 60px; /* Adjust according to navbar height */
            bottom: 0;
            background-color: #333;
            color: white;
            width: 200px;
            box-sizing: border-box;
            transition: right 0.3s ease;
            padding: 20px;
            z-index: 999; /* Ensure sidebar is above other content */
        }
        .sidebar.open {
            right: 0; /* Slide in when open */
        }

        /* Form styles */
        #report-form {
            display: flex;
            flex-direction: column;
        }

        #report-form label {
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 14px;
        }

        #report-form select {
            margin-bottom: 20px;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #fff;
        }

        #report-form button {
            padding: 10px 15px;
            font-size: 14px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        #report-form button:hover {
            background-color: #0056b3;
        }

        #map {
            flex: 1; /* Take remaining space */
            margin-top: 60px; /* Adjust according to navbar height */
        }
        .header {
            text-align: center;
        }
        .image-container {
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
        }
        .image-container img {
            max-width: 50%;
            height: auto;
            margin-right: 20px;
        }
        .gps-data {
            max-width: 50%;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="menu-items">
            <div id="map-view" class="menu-item active">Map View</div>
            <div id="object-detection-view" class="menu-item">Object Detection View</div>
        </div>
        <div class="hamburger-menu" id="hamburger-menu">☰</div>
    </div>
    <div id="sidebar" class="sidebar">
        <form id="report-form" method="post" action="generate_pdf.php">
        <label for="report_period">Pilih Periode Laporan:</label>
        <select name="report_period" id="report_period">
            <option value="day">Harian</option>
            <option value="week">Mingguan</option>
        </select>
        <button type="submit">Download PDF</button>
        </form>
    </div>
    <div id="map" style="height: 500px;"></div>
    <div id="object-detection-results" class="hidden">
        <h1 class="header">YOLOv5 Detection Results</h1>
        <div class="image-container">
            <?php
            error_reporting(E_ALL);
            ini_set('display_errors', 1);

            $db_host = "localhost";
            $db_user = "root";
            $db_password = "";
            $db_name = "workforce_tracker";

            // Create connection
            $connection = new mysqli($db_host, $db_user, $db_password, $db_name);

            // Check connection
            if ($connection->connect_error) {
                die("Connection failed: " . $connection->connect_error);
            }

            // SQL query to fetch image, GPS data, and user information
            $query = "SELECT d.image, g.id AS gps_id, g.status, g.timestamp AS gps_timestamp, g.latitude, g.longitude, 
                    u.user_name, u.job_division 
                FROM deteksiyolo d 
                RIGHT JOIN gps_data g ON d.id = g.id
                JOIN user_gps u ON g.device_id = u.device_id
                ORDER BY d.timestamp DESC 
                LIMIT 1;
                ";

            // Execute the query
            $result = $connection->query($query);

            if (!$result) {
                die("Query failed: " . $connection->error);
            }

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // // Debugging output
                    // echo '<pre>';
                    // var_dump($row);
                    // echo '</pre>';

                    // Check if image data is present
                    if (!empty($row["image"])) {
                        // Retrieve and decode base64-encoded image data
                        $image_data = base64_decode($row["image"]);

                        if ($image_data === false) {
                            echo "<p>Error decoding image data.</p>";
                        } else {
                            // Display image using HTML <img> tag
                            echo '<div class="image-container">';
                            echo '<img src="data:image/jpeg;base64,' . base64_encode($image_data) . '" />';
                            echo '<div class="gps-data">';
                            echo '<p><strong>Nama:</strong> ' . htmlspecialchars($row["user_name"] ?? 'N/A') . '</p>';
                            echo '<p><strong>Divisi:</strong> ' . htmlspecialchars($row["job_division"] ?? 'N/A') . '</p>';
                            echo '<p><strong>Status:</strong> ' . htmlspecialchars($row["status"] ?? 'N/A') . '</p>';
                            echo '<p><strong>Waktu:</strong> ' . htmlspecialchars($row["gps_timestamp"] ?? 'N/A') . '</p>';
                            echo '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo "<p>No image data found.</p>";
                    }
                }
            } else {
                echo "<p>No image or GPS data found.</p>";
            }

            // Close the connection
            $connection->close();
            ?>
        </div>
    </div>



    <!-- Add Leaflet and Axios scripts -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <!-- Your script -->
    <script src="script2.js"></script>
</body>
</html>
