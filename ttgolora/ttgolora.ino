#include <SPI.h>
#include <LoRa.h>
#include <TinyGPS++.h>
#include <SoftwareSerial.h>

// LoRa pins for TTGO LoRa v2.1.6
#define SCK     5     // GPIO5  -- LoRa SCK
#define MISO    19    // GPIO19 -- LoRa MISO
#define MOSI    27    // GPIO27 -- LoRa MOSI
#define SS      18    // GPIO18 -- LoRa CS
#define RST     14    // GPIO14 -- LoRa RESET
#define DIO0    26    // GPIO26 -- LoRa IRQ (Interrupt Request)
#define BAND    433E6 // LoRa Frequency

// GPS pins
#define rxGPS   22
#define txGPS   23

TinyGPSPlus gps;
SoftwareSerial gpsSerial(rxGPS, txGPS);

unsigned long lastTransmissionTime = 0;
const unsigned long transmissionInterval = 2000; // Interval between transmissions in milliseconds

// Geofence coordinates
const double fences[1][10][2] = {
    {{-6.304849196732984, 107.31428582615027},
     {-6.304846700444834, 107.3143670306176},
     {-6.304846700444834, 107.31444823508492},
     {-6.304914100220505, 107.31445074656328},
     {-6.304983164179073, 107.31445493236059},
     {-6.305026433160483, 107.31443651485252},
     {-6.3050305936392785, 107.31435614754464},
     {-6.305031425735027, 107.31429587206375},
     {-6.304992317233138, 107.31428666330972},
     {-6.304913268124565, 107.31428750046916}}
};

// Function to check if the current location is inside the geo-fence
bool isInsideGeoFence(double latitude, double longitude) {
    int fenceSize = sizeof(fences[0]) / sizeof(fences[0][0]);
    double vectors[fenceSize][2];
    for (int i = 0; i < fenceSize; i++) {
        vectors[i][0] = fences[0][i][0] - latitude;
        vectors[i][1] = fences[0][i][1] - longitude;
    }
    double angle = 0;
    double num, den;
    for (int i = 0; i < fenceSize; i++) {
        num = (vectors[i % fenceSize][0]) * (vectors[(i + 1) % fenceSize][0]) + (vectors[i % fenceSize][1]) * (vectors[(i + 1) % fenceSize][1]);
        den = (sqrt(pow(vectors[i % fenceSize][0], 2) + pow(vectors[i % fenceSize][1], 2))) * (sqrt(pow(vectors[(i + 1) % fenceSize][0], 2) + pow(vectors[(i + 1) % fenceSize][1], 2)));
        angle += (180 * acos(num / den) / M_PI);
    }
    return (angle > 355 && angle < 365);
}

void setup() {
    Serial.begin(9600);
    gpsSerial.begin(9600);

    // Setup LoRa transceiver module
    SPI.begin(SCK, MISO, MOSI, SS);
    pinMode(RST, OUTPUT);
    digitalWrite(RST, HIGH);

    Serial.println("LoRa Transmitter");

    // Initialize LoRa
    LoRa.setPins(SS, RST, DIO0);
    if (!LoRa.begin(BAND)) {
        Serial.println("Starting LoRa failed!");
        while (1);
    }
    Serial.println("LoRa Initial OK!");
}

void loop() {
    // Check if enough time has passed since the last transmission
    if (millis() - lastTransmissionTime > transmissionInterval) {
        while (gpsSerial.available()) {
            if (gps.encode(gpsSerial.read())) {
                if (gps.location.isValid()) {
                    float lat = gps.location.lat();
                    float lon = gps.location.lng();
                    String status;
                    String deviceId = "Device1"; // Change this to the correct device ID
                    unsigned long timestamp = millis();

                    // Check if the current location is inside the geo-fence
                    if (isInsideGeoFence(lat, lon)) {
                        status = "Inside geofence";
                        Serial.println("Inside geofence.");
                    } else {
                        status = "Outside geofence";
                        Serial.println("Outside geofence.");
                    }

                    // Transmit the data
                    LoRa.beginPacket();
                    LoRa.write((uint8_t*)&lat, sizeof(float)); // Write latitude as a byte array
                    LoRa.write((uint8_t*)&lon, sizeof(float)); // Write longitude as a byte array
                    LoRa.print(status); // Write status as a string
                    LoRa.print(","); // Separator
                    LoRa.print(deviceId); // Write device ID
                    LoRa.print(","); // Separator
                    LoRa.write((uint8_t*)&timestamp, sizeof(unsigned long)); // Write timestamp
                    LoRa.endPacket();

                    // Update the last transmission time
                    lastTransmissionTime = millis();
                    break; // Exit the while loop once data is sent
                }
            }
        }
    }
}
