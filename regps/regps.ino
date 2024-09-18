#include <SPI.h>
#include <LoRa.h>
#include <WiFi.h>
#include <HTTPClient.h>

// LoRa pins
#define SCK     18    // GPIO5  -- lora SCK
#define MISO    19   // GPIO19 -- lora MISO
#define MOSI    23  // GPIO27 -- lora MOSI
#define SS      5   // GPIO18 -- lora CS
#define BAND    433E6 // LoRa Frequency

// WiFi credentials
const char* ssid = "Kamu neanya ?";
const char* password = "00000001";

// Server URL
const char* serverUrl = "http://192.168.134.222:3000/data";

// LED pin
#define LED_PIN 2

void setup() {
  Serial.begin(9600);
  pinMode(LED_PIN, OUTPUT);

  // Connect to WiFi
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }
  Serial.println("Connected to WiFi");

  SPI.begin(SCK, MISO, MOSI, SS);
  LoRa.setPins(SS, -1, -1);  // Not using RST and DIO0
  Serial.println("LoRa Receiver");

  if (!LoRa.begin(BAND)) {
    Serial.println("Starting LoRa failed!");
    while (1);
  }
  Serial.println("LoRa Initial OK!");
}

void sendToServer(float lat, float lon, const String& status, const String& deviceId, unsigned long delay) {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverUrl);
    http.addHeader("Content-Type", "application/json");

    String jsonPayload = "{\"latitude\":" + String(lat, 6) + ",\"longitude\":" + String(lon, 6) + ",\"status\":\"" + status + "\",\"device_id\":\"" + deviceId + "\",\"delay\":" + String(delay) + "}";

    int httpResponseCode = http.POST(jsonPayload);

    if (httpResponseCode > 0) {
      String response = http.getString();
      Serial.println("Server response: " + response);
    } else {
      Serial.println("Error on sending POST: " + String(httpResponseCode));
    }

    http.end();
  } else {
    Serial.println("WiFi not connected");
  }
}

void loop() {
  // try to parse packet
  int packetSize = LoRa.parsePacket();
  if (packetSize) {
    // RSSI
    int rssi = LoRa.packetRssi();
    Serial.print("RSSI: ");
    Serial.println(rssi);

    Serial.println("Received packet!");
    digitalWrite(LED_PIN, HIGH);  // Turn on the LED

    // Read latitude
    float receivedLat;
    LoRa.readBytes((uint8_t*)&receivedLat, sizeof(float));
    Serial.print("Received Latitude: ");
    Serial.println(receivedLat, 6); // Print latitude with 6 decimal places

    // Read longitude
    float receivedLon;
    LoRa.readBytes((uint8_t*)&receivedLon, sizeof(float));
    Serial.print("Received Longitude: ");
    Serial.println(receivedLon, 6); // Print longitude with 6 decimal places

    // Read status
    String status = "";
    while (LoRa.available()) {
      char ch = (char)LoRa.read();
      if (ch == ',') break; // Assuming the status is followed by a comma
      status += ch;
    }
    Serial.print("Received Status: ");
    Serial.println(status);

    // Read device ID
    String deviceId = "";
    while (LoRa.available()) {
      char ch = (char)LoRa.read();
      if (ch == ',') break; // Assuming the device ID is followed by a comma
      deviceId += ch;
    }
    Serial.print("Received Device ID: ");
    Serial.println(deviceId);

    // Read timestamp
    unsigned long receivedTimestamp;
    LoRa.readBytes((uint8_t*)&receivedTimestamp, sizeof(unsigned long));
    Serial.print("Received Timestamp: ");
    Serial.println(receivedTimestamp);
    
    // Calculate delay
    unsigned long delay = millis() - receivedTimestamp;
    Serial.print("Calculated Delay: ");
    Serial.println(delay);

    if (receivedLat != 0 && receivedLon != 0) {
      // Send data to server
      sendToServer(receivedLat, receivedLon, status, deviceId, delay);
    } else {
      Serial.println("Latitude or longitude is null");
    }

    digitalWrite(LED_PIN, LOW);  // Turn off the LED
  }
}
