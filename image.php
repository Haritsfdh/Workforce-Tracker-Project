<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YOLOv5 Detection Results</title>
    <style>
        /* Your existing CSS styles */
        .result-container {
            margin-bottom: 20px;
            border: 1px solid #ccc;
            padding: 10px;
            overflow: hidden;
        }
        .result-container img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <!-- Your existing HTML structure -->
    <div class="navbar">
        <!-- Navbar content -->
    </div>

    <div class="sidebar">
        <!-- Sidebar content -->
    </div>

    <div id="map">
        <!-- Map content -->
    </div>

    <div id="object-detection-results">
        <h1 class="header">YOLOv5 Detection Results</h1>
        <?php
        $db_host = "localhost";
        $db_user = "root";
        $db_password = "";
        $db_name = "yolo";

        // Create connection
        $connection = new mysqli($db_host, $db_user, $db_password, $db_name);

        // Check connection
        if ($connection->connect_error) {
            die("Connection failed: " . $connection->connect_error);
        }

        // SQL query to fetch latest 30 images
        $query = "SELECT image FROM deteksiyolo ORDER BY timestamp DESC LIMIT 30";
        $result = $connection->query($query);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Retrieve and decode base64-encoded image data
                $image_data = base64_decode($row["image"]);
                // Display image using HTML <img> tag
                echo '<img src="data:image/jpeg;base64,' . base64_encode($image_data) . '" />';
            }
        } else {
            echo "No image data found.";
        }

        $connection->close();
        ?>


    </div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        // Your existing JavaScript code
    </script>
</body>
</html>
