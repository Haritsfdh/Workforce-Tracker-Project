<?php
require('fpdf186/fpdf.php');

// Database connection
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

// Function to fetch GPS data based on the report period
function fetchGPSData($connection, $period) {
    if ($period == 'day') {
        $query = "SELECT * FROM gps_data WHERE timestamp >= CURDATE()";
    } else if ($period == 'week') {
        $query = "SELECT * FROM gps_data WHERE timestamp >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    }

    $result = $connection->query($query);
    $data = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    
    return $data;
}

// Function to fetch person data
function fetchPersonData($connection) {
    $query = "SELECT * FROM user_gps"; // Adjust table and field names
    $result = $connection->query($query);
    $data = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    return $data;
}

// Check if report period is set
if (isset($_POST['report_period'])) {
    $period = $_POST['report_period'];

    class PDF extends FPDF
    {
        // Page header
        function Header()
        {
            $this->SetFont('Arial', 'B', 15);
            $this->Cell(0, 10, 'PDF Report', 0, 1, 'C');
            $this->Ln(10);
        }

        // Page footer
        function Footer()
        {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
        }

        // Custom function to add person data
        function PersonData($personData)
        {
            $this->SetFont('Arial', '', 12);
            foreach ($personData as $person) {
                $this->Cell(0, 10, 'Nama: ' . $person['user_name'], 0, 1, 'C');
                $this->Cell(0, 10, 'Divisi: ' . $person['job_division'], 0, 1, 'C');
                $this->Ln(10);
            }
        }

        // Custom function to add GPS data report content
        function ReportContent($period, $data)
        {
            $this->SetFont('Arial', '', 12);
            $this->Cell(0, 10, 'Laporan : ' . ucfirst($period), 0, 1, 'C');
            $this->Ln(10);

            $tableWidth = 120; // Total width of the table
            $this->SetX(($this->w - $tableWidth) / 2); // Center the table
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(30, 10, 'ID', 1, 0, 'C');
            $this->Cell(50, 10, 'Status', 1, 0, 'C');
            $this->Cell(40, 10, 'Timestamp', 1, 0, 'C');
            $this->Ln();

            $this->SetFont('Arial', '', 12);
            foreach ($data as $row) {
                $this->SetX(($this->w - $tableWidth) / 2); // Center the table
                $this->Cell(30, 10, $row['id'], 1, 0, 'C');
                $this->Cell(50, 10, $row['status'], 1, 0, 'C');
                $this->Cell(40, 10, $row['timestamp'], 1, 0, 'C');
                $this->Ln();
            }
        }
    }

    // Fetch GPS data based on the selected period
    $gpsData = fetchGPSData($connection, $period);

    // Fetch person data
    $personData = fetchPersonData($connection);

    // Create new PDF document
    $pdf = new PDF();
    $pdf->AddPage();

    // Add person data
    $pdf->PersonData($personData);

    // Add GPS data report content
    $pdf->ReportContent($period, $gpsData);

    // Output the PDF document
    $pdf->Output('D', 'report_' . $period . '.pdf');
} else {
    echo 'Report period not specified.';
}

$connection->close();
?>
