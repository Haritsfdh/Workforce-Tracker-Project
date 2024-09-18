const express = require('express');
const bodyParser = require('body-parser');
const mysql = require('mysql');
const WebSocket = require('ws');
const axios = require('axios');
const path = require('path');
const fs = require('fs');
const PDFDocument = require('pdfkit');

const app = express();
const port = 3000;

app.use(bodyParser.json({
    strict: true, // Enable strict mode to reject invalid JSON
    limit: '1mb' // Set a limit on the size of JSON payloads
}));

const db = mysql.createConnection({
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'workforce_tracker'
});

db.connect((err) => {
    if (err) throw err;
    console.log('Connected to database');
});

app.post('/data', (req, res) => {
    console.log('Received request body:', req.body); // Log the raw request body

    const { latitude, longitude, status, device_id } = req.body;

    if (latitude == null || longitude == null) {
        return res.status(400).json({ error: 'Latitude and longitude cannot be null' });
    }

    const query = 'INSERT INTO gps_data (latitude, longitude, status, device_id) VALUES (?, ?, ?, ?)';
    db.query(query, [latitude, longitude, status, device_id], (error, results) => {
        if (error) {
            console.error('Error inserting data:', error);
            return res.status(500).json({ error: 'Database insert failed' });
        }
        res.status(200).json({ success: true, data: results });
    });
});

// Function to reverse geocode latitude and longitude to an address
async function reverseGeocode(latitude, longitude) {
    try {
        const response = await axios.get('https://nominatim.openstreetmap.org/reverse', {
            params: {
                format: 'jsonv2',
                lat: latitude,
                lon: longitude
            }
        });
        return response.data.display_name;
    } catch (error) {
        console.error('Error during reverse geocoding:', error);
        return 'Address not found';
    }
}

// WebSocket server
const wss = new WebSocket.Server({ noServer: true });

wss.on('connection', (ws) => {
    console.log('Client connected');

    // Send GPS data updates every 2 seconds
    const interval = setInterval(async () => {
        const query = `
            SELECT gps_data.latitude, gps_data.longitude, gps_data.status, gps_data.device_id, user_gps.user_name, user_gps.job_division
            FROM gps_data
            JOIN user_gps ON gps_data.device_id = user_gps.device_id
            ORDER BY gps_data.timestamp DESC LIMIT 1
        `;
        db.query(query, async (error, results) => {
            if (error) {
                console.error('Error fetching data:', error);
                return;
            }

            if (results.length > 0) {
                const data = results[0];
                const address = await reverseGeocode(data.latitude, data.longitude);
                data.address = address;
                ws.send(JSON.stringify(data));
                console.log('Sent data:', JSON.stringify(data)); // Log the sent data
            }
        });
    }, 2000);

    ws.on('close', () => {
        clearInterval(interval);
    });
});

app.get('/download-report', (req, res) => {
    const period = req.query.period;
    const doc = new PDFDocument();
    const fileName = `report_${period}.pdf`;
    const filePath = path.join(__dirname, fileName);

    doc.pipe(fs.createWriteStream(filePath));
    doc.fontSize(25).text('GPS Tracker Report', { align: 'center' });

    const userQuery = 'SELECT * FROM user_gps';
    db.query(userQuery, (userError, userResults) => {
        if (userError) {
            console.error('Error fetching user data:', userError);
            return res.status(500).json({ error: 'Database query failed' });
        }

        userResults.forEach(user => {
            doc.fontSize(14).text(`ID: ${user.id}`);
            doc.text(`Name: ${user.user_name}`);
            doc.text(`Job Division: ${user.job_division}`);
            doc.text(`Device ID: ${user.device_id}`);
            doc.moveDown();
        });

        let query;
        if (period === 'day') {
            query = `
                SELECT gps_data.latitude, gps_data.longitude, gps_data.status, gps_data.device_id, user_gps.user_name, user_gps.job_division, gps_data.timestamp
                FROM gps_data
                JOIN user_gps ON gps_data.device_id = user_gps.device_id
                WHERE gps_data.timestamp >= CURDATE()
                ORDER BY gps_data.timestamp DESC
            `;
        } else if (period === 'week') {
            query = `
                SELECT gps_data.latitude, gps_data.longitude, gps_data.status, gps_data.device_id, user_gps.user_name, user_gps.job_division, gps_data.timestamp
                FROM gps_data
                JOIN user_gps ON gps_data.device_id = user_gps.device_id
                WHERE gps_data.timestamp >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)
                ORDER BY gps_data.timestamp DESC
            `;
        } else {
            return res.status(400).json({ error: 'Invalid period' });
        }

        db.query(query, async (error, results) => {
            if (error) {
                console.error('Error fetching data:', error);
                res.status(500).json({ error: 'Database query failed' });
                return;
            }

            results.forEach(async (row, index) => {
                const address = await reverseGeocode(row.latitude, row.longitude);
                doc.fontSize(14).text(`Record ${index + 1}:`);
                doc.text(`User: ${row.user_name}`);
                doc.text(`Job Division: ${row.job_division}`);
                doc.text(`Address: ${address}`);
                doc.text(`Status: ${row.status}`);
                doc.text(`Timestamp: ${row.timestamp}`);
                doc.text('--------------------------------');
            });

            doc.end();

            doc.on('finish', () => {
                res.download(filePath, fileName, (err) => {
                    if (err) {
                        console.error('Error downloading file:', err);
                        res.status(500).json({ error: 'File download failed' });
                    } else {
                        fs.unlink(filePath, (err) => {
                            if (err) {
                                console.error('Error deleting file:', err);
                            }
                        });
                    }
                });
            });
        });
    });
});

// Create HTTP server
const server = app.listen(port, () => {
    console.log(`Server running on port ${port}`);
});

// Upgrade HTTP server to WebSocket server
server.on('upgrade', (request, socket, head) => {
    wss.handleUpgrade(request, socket, head, (ws) => {
        wss.emit('connection', ws, request);
    });
});
