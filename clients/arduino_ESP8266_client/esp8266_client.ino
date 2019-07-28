/**
    Copyright (c) 2019, dsphinx@plug.gr
    All rights reserved.

    Redistribution and use in source and binary forms, with or without
    modification, are permitted provided that the following conditions are met:
     1. Redistributions of source code must retain the above copyright
        notice, this list of conditions and the following disclaimer.
     2. Redistributions in binary form must reproduce the above copyright
        notice, this list of conditions and the following disclaimer in the
        documentation and/or other materials provided with the distribution.
     3. All advertising materials mentioning features or use of this software
        must display the following acknowledgement:
        This product includes software developed by the dsphinx.
     4. Neither the name of the dsphinx nor the
        names of its contributors may be used to endorse or promote products

    Created by Constantinos on 6/6/19.
    Filename : WeMos D1 mini DHT22.h

    Arduino family:  WeMos D1 Mini - ESP8266   ESP-12 ESP-12F CH340G CH340
    Αισθητήρας Θερμοκρασίας Υγρασίας :   DHT22 ή DHT11

    συνδεσμολογία -  Pins:

     D0     <--> RST                 για  ESP8266 deepsleep - εξοικονόμιση ενέργειας
     DHTPIN <--> D6                  data pin μεταφορά δεδομένων
     3.3V ,    Ground


   troubleshooting -  hard rest

     RESET <--> D3  ή   RESET <--> D1

 **/


#include <ESP8266WiFi.h>        // αναφορά - https://arduino-esp8266.readthedocs.io/en/latest/esp8266wifi/readme.html
#include <DHT.h>

template<typename T, size_t N> size_t ArraySize(T (&)[N]) {
  return N;
}

#define dsphinXVersion    0.72
#define DHTTYPE DHT22         //   Αισθητήρας Θερμοκρασίας Υγρασίας :   DHT22 (AM2302)  ή DHT11
#define DHTPIN  D6            //   data pin μεταφορά δεδομένων

/*
    για mode development και production
*/
//#define devMode                              1    // developer mode - testing

#ifdef devMode
#define Wifi_Desc                         "devtesting "
#define WiFi_ClientName                   "Sensor-A"           // optional name - ΧΩΡΙΣ ΚΕΝΑ το όνομα
#endif

#ifndef devMode
#define Wifi_Desc                         "Computer Lab on school"
#define WiFi_ClientName                   "Lab-PC"    // optional name - ΧΩΡΙΣ ΚΕΝΑ το όνομα
#endif

DHT dht(DHTPIN, DHTTYPE, 15);               // αρχικοποίηση για χρήση DHT αισθητήρα ESP8266

/*
   Καθολικές μεταβλητές

*/

//const int sleepTimeS = 600;                // σε δευτερόλεπτα - DEEP SLEEP - εξοικονόμιση ενέργειας
const int sleepTimeS = 5;                // σε δευτερόλεπτα - DEEP SLEEP - εξοικονόμιση ενέργειας

float prevTemp = 0;
long sent = 0;
bool debug = true;                         // output σε serial monitor ?
int maxSensorTry = 2000, sensorI = 0;       // προσπάθειας  για σύνδεση με τπν αισθητήρα

/*
     Ρυθμίσεις για συνδέσεις σε πολλαπλά Wifi δίκτυα, σε περίπτωση αποτυχίας σύνδεσης στο κύριο

*/
String WifiNet[][2] = {  
  { "DimPirgon2", "xxx" },
  { "DimPirgon1", "xxx" },
  { "ssd1", "xxx" },
  { "ssd2", "xxx" },
  { "home", "xxx" },
  { "biz", "xxx" },
};


/*
    URI για end point, αποστολή στοιχείων σε Data collecting servers
*/
String CollectionServer[][3] = {
  {"SERVER_IP_OR_DNS", "index.php?option=com_ajax&module=temperature&format=raw", "80"},
};

/**
    μηνύματα προς serial monitor, σε κατάσταση true

   @param message
*/
void show(String message) {
  if (debug) {
    Serial.println(message);
  }
}


/**
    Άναψε / Σβήσε το μοναδικό LED

*/
void led(int turnOn = 1) {
  if (debug) {
    if (turnOn == 1) {
      digitalWrite(BUILTIN_LED, HIGH);
    } else {
      digitalWrite(BUILTIN_LED, LOW);
    }
  }
}



/**
    σύνδεση σε κάποιο απο τα γνωστά Wifi δίκτυα που βρίσκονται σε WifiNet
*/
void connectWifi() {

  int maxTry = 5;                          // απόπειρες προσπάθειας - εξοικονόμιση ενέργειας
  int n, i, x;
  char ssid[20], pass[30];
  String tmp;

  n = ArraySize(WifiNet);
  /*
      χρήση απλή ως station - client,
      μπορεί να δουλεύψει και ως Iot, mess κοκ

      πληροφορίες : https://arduino-esp8266.readthedocs.io/en/latest/esp8266wifi/readme.html
  */
  WiFi.mode(WIFI_STA);                     // Client only -  station


  for (i = 0; i < n; i++) {

    tmp = WifiNet[i][0];
    tmp.toCharArray(ssid, tmp.length() + 1);
    tmp = WifiNet[i][1];
    tmp.toCharArray(pass, tmp.length() + 1);

    show("σύνδεση με  " + (String) ssid);

    WiFi.begin(ssid, pass);
    delay(1000);
    show(".");

    for (x = 0; x < maxTry; x++) {

      delay(1000);

      if (WiFi.status() != WL_CONNECTED) {
      } else {
        show("");
        Serial.println(" OK " + (String) WifiNet[i][0]);
        show("");
        Serial.println(WiFi.localIP());
        return;
      }
    }
    show(" αποτυχία ");
  }

  if (WiFi.status() != WL_CONNECTED) {
    show("");
    show(" πλήρης αποτυχία σύνδεσης σε Wifi  ");
    show("");
  }


}


/**

    Αποστολή δεδομένων συλλογής απο αισθητήρες στα γνωστά end points - CollectionServer

   @param temp1
   @param temp2
   @param Host
   @param Port
   @param Script
   @param Proto
*/
void sendDataToServer(float temp1, float temp2, String Host = "", int Port = 80, String Script = "",
                      String Proto = "http://") {

  WiFiClient client;
  int i, n;

  n = ArraySize(CollectionServer);


  for (i = 0; i < n; i++) {

    Host = (String) CollectionServer[i][0];
    Port = CollectionServer[i][2].toInt();
    Script = (String) CollectionServer[i][1];


    show(" σύνδεση σε server -  " + Host);

    if (client.connect(Host, Port)) {

      String uri = "";
      unsigned long timeout = millis();

      show("Client connected ");
      /*
          uri  = API END POINT
                 πλήρης διεύθυνση του PHP script συλλογής δεδομένων
      */
      uri = Proto + Host + ":" + Port + Script + "?Temperature=" + (String) temp1 + "&Humidity=" + temp2 + "&esp8266ID=" + String(ESP.getChipId()) +
            "&sensorName=" + (String) WiFi_ClientName + "&Description=" + (String) Wifi_Desc + "&ver=" +
            (String) dsphinXVersion;
      show(uri);

      /*
          HTTP 1.1 - web protocol
      */
      client.print(String("GET ") + uri + " HTTP/1.1\r\n" +
                   "Host: " + Host + "\r\n" +
                   "Connection: close\r\n\r\n");

      while (client.available() == 0) {
        if (millis() - timeout > 5000) {
          show(">>> Client Timeout !");
          client.stop();
          break;
        }
      }

      delay(2000);
      sent++;
    } else {
      show(" αποτυχία σύνδεσης σε end point !");
    }

    delay(2000);
  }
  // flush();

  client.stop();
}


/*
    Αρχικοποίηση Arduino - WeMos D1 mini

    Εκτέλεση
                1) Αρχικά
                2) μετά από κάθε Wake Up απο το DeepSleep
*/
void setup() {
  Serial.begin(115200);               // baud rate για serial monitor
  dht.begin();
  if (debug) {
    pinMode(BUILTIN_LED, OUTPUT);    // initialize onboard LED as output
  }
  led(1);
//statusESP();
  // pinMode(D0, WAKEUP_PULLUP);  // Connect D0 to RST to wake up

  connectWifi();
  show("Setup init ");
}


/*
    Ανάγνωση δεδομένων συλλογής από αισθητήτρες
                       και αποστολή στα end point
                       συνεχόμενα deep sleep για εξοικονόμιση ενέργειας

    Ατέρμων βρόχος - κύρια διαδικασία υλοποίησης
*/
void loop() {

  String ret;
  float t1, humidity, heatIndex;
  humidity = dht.readHumidity();            // Ανάγνωση δεδομένων υγρασίας     ( ποσοστιαία )
  t1 = dht.readTemperature();               // Ανάγνωση δεδομένων θερμοκρασίας ( ποσοστιαία )

  /*
     συνδυαστικός δείκτης θερμοκρασίας

     πηγή: https://en.wikipedia.org/wiki/Heat_index
  */
  heatIndex = dht.computeHeatIndex(t1, humidity, false);  // συνδυαστικό

  ret = "Υγρασία: " + (String) humidity + " %";
  ret += " Θερμοκρασία: " + (String) t1 + "  " + " Συνδυαστικός Δείκτης Θεμροκρασίας : " + heatIndex;

  if ((isnan(humidity) || isnan(t1)) && (sensorI < maxSensorTry)) {
    sensorI++;
    Serial.println((String) sensorI + " αποτυχία σύνδεση σε  from DHT sensor!");
    return;
  }

  show(ret);
  sendDataToServer(heatIndex, humidity);

  if (debug) {
    statusESP();
  }
  led(0);
  show("ώρα για βαθύ ύπνο ;) ");
  // WAKE_RF_DEFAULT, WAKE_RFCAL, WAKE_NO_RFCAL, WAKE_RF_DISABLED
  ESP.deepSleep(sleepTimeS * 1000000, WAKE_RF_DEFAULT);           // βαθύ ύπνο για sleepTimeS δευτερόλεπτα
}



/**
    Info about chip
*/
void statusESP() {
  //   WiFi.printDiag(Serial)WiFi.printDiag(Serial);
  rst_info *rinfo = ESP.getResetInfoPtr();

  Serial.print(String("\nResetInfo.reason = ") + (*rinfo).reason + ": " + ESP.getResetReason() + "\n");

  Serial.print("ESP8266 chip ID " + String(ESP.getChipId()) + " B\n");
  Serial.print("ESP core version: " + String(ESP.getCoreVersion()) + " B\n");

  Serial.print("SDK core version: " + String(ESP.getSdkVersion()) + " B\n");
  Serial.print("CPU Mhz: " + String(ESP.getCpuFreqMHz()) + " B\n");
  Serial.print("Sketch size : " + String(ESP.getSketchSize()) + " B\n");
  Serial.print("free Sketch size : " + String(ESP.getFreeSketchSpace()) + " B\n");


  Serial.print("free heap: " + String(ESP.getFreeHeap()) + " B\n");
  Serial.print("flash chip size: " + String(ESP.getFlashChipSize()) + " B\n");
  Serial.print("frequency HZ chip  : " + String(ESP.getFlashChipSpeed()) + " B\n");
  Serial.print("cpu instruction cycle count since start  : " + String(ESP.getCycleCount()) + " B\n");

}
