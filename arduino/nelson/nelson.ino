/*
    This sketch sends data via HTTP GET requests to data.sparkfun.com service.

    You need to get streamId and privateKey at data.sparkfun.com and paste them
    below. Or just customize this script to talk to other HTTP servers.

*/

#include <ESP8266WiFi.h>
#include <Servo.h>

Servo myservo;

const char* ssid     = "****";
const char* password = "****";

const char* host = "****";
String keyword = String("\"position\":");
String positionString = "";

void setup() {
  Serial.begin(9600);
  delay(10);

  // We start by connecting to a WiFi network

  Serial.println();
  Serial.println();
  Serial.print("Connecting to ");
  Serial.println(ssid);

  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("");
  Serial.println("WiFi connected");
  Serial.println("IP address: ");
  Serial.println(WiFi.localIP());
}

int value = 0;
bool inBody = false;

void loop() {
  delay(5000);
  ++value;
  positionString = "";

  Serial.print("connecting to ");
  Serial.println(host);

  // Use WiFiClient class to create TCP connections
  WiFiClient client;
  const int httpPort = 80;
  if (!client.connect(host, httpPort)) {
    Serial.println("connection failed");
    return;
  }

  // We now create a URI for the request
  String url = String("/nelson-io/api/nelson/0");

  Serial.print("Requesting URL: ");
  Serial.println(url);

  // This will send the request to the server
  client.print(String("GET ") + url + " HTTP/1.1\r\n" +
               "Host: " + host + "\r\n" +
               "Connection: close\r\n\r\n");

  unsigned long timeout = millis();
  while (client.available() == 0) {
    if (millis() - timeout > 5000) {
      Serial.println(">>> Client Timeout !");
      client.stop();
      return;
    }
  }

  while (client.available()) {
    String line = client.readStringUntil('\r');

    if (line.length() == 1) inBody = true;
    if (inBody) {
      int pos = line.indexOf(keyword);

      if (pos > 0) {
        // indexOf donne la position du début du mot clé, en ajoutant sa longueur
        // on se place à la fin.
        pos += keyword.length() + 1;

        int end = line.indexOf('\"', pos );

        for (int i = pos; i < end; i++)
          positionString += line.charAt(i);

        Serial.println (positionString);

      }
    }
  }

  Serial.println(); Serial.print ("Position = "); Serial.println(positionString.toInt()); // temp en Kelvin

  myservo.attach(D1);
  myservo.write(positionString.toInt());
  delay(500);
  myservo.detach();


  Serial.println();
  Serial.println("closing connection");
}

