
// Importamos las librerías
#include <ESP8266WiFi.h>
#include <ESPAsyncWebServer.h>
#include <ESPAsyncTCP.h>
#include <ESPAsyncWiFiManager.h> //https://github.com/tzapu/WiFiManager
#include <FS.h>
#include <Arduino.h>
#include <ESP8266HTTPClient.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>

LiquidCrystal_I2C lcd(0x27, 16, 2);

// Puesta de LED GPIO
const int ledPin = 2;
// Para guardar el estado del LED
String ledState;

// Creamos el servidor AsyncWebServer en el puerto 80
AsyncWebServer server(80);
DNSServer dns;

// Variasbles de pines

#define coin D5
#define button D6
#define rest D7
int coin1 = LOW;
int button1 = LOW;
int rest1 = LOW;
String jwt;

// Variables para las funciones fisicas

int red = 0;
int blue = 0;
int green = 0;
int key = 0;
long randomNumber;
int contador = 0;
int e = 0;
int sesion = 0;
int MIN = 30;
String clave;
String minutos;
String user = "juan";
String pass = "1234";
String ip = "192.168.1.3";
String ipM = "192.168.1.2";

//-------------------el gestor de wifi----------------------
void mangerWifi()
{
  Serial.println("Conectando a la red WiFi...");
  if (rest1 != 0)
  {
    // Descomentar para resetear configuración
    Serial.println("Borrando configuración");
  }
  AsyncWiFiManager wifiManager(&server, &dns);
  delay(1000);
  
  if (rest1 != 0)
  {
    // Descomentar para resetear configuración
    wifiManager.resetSettings();
    Serial.println("Borrado configuración");
  }
  wifiManager.autoConnect("AutoConnect");
  LCD("AutoConnect", "192.168.4.1", 0);
}

//------------------el cargador de rutas-------------------
void cargaRutas()
{
  // Ruta para cargar el archivo index.html
  server.on("/", HTTP_GET, [](AsyncWebServerRequest *request)
            { request->send(SPIFFS, "/index.html", String(), false, processor); });

  // Ruta para cargar el archivo style.css
  server.on("/style.css", HTTP_GET, [](AsyncWebServerRequest *request)
            { request->send(SPIFFS, "/style.css", "text/css"); });

  // Ruta para poner el GPIO alto
  server.on("/on", HTTP_GET, [](AsyncWebServerRequest *request)
            {
    digitalWrite(ledPin, HIGH);
    MIN = MIN + 15;
    request->send(SPIFFS, "/index.html", String(), false, processor); });

  // Ruta para poner el GPIO bajo
  server.on("/off", HTTP_GET, [](AsyncWebServerRequest *request)
            {
    digitalWrite(ledPin, LOW);
    if (MIN >= 30) {
      MIN = MIN - 15;
    }
    request->send(SPIFFS, "/index.html", String(), false, processor); });
}

// Remplazamos el marcador con el estado del  LED
String processor(const String &var)
{
  Serial.println(var);
  if (var == "STATE")
  {
    if (digitalRead(ledPin))
    {
      ledState = String(MIN) + "min";
    }
    else
    {
      ledState = String(MIN) + "min";
    }
    // Imprimimos el estado del led ( por el COM activo )
    Serial.print(ledState);
    return ledState;
  }
}

//------------------Setuput-----------------------------
void setup()
{

  // Iniciamos  SPIFFS
  if (!SPIFFS.begin())
  {
    Serial.println("ha ocurrido un error al montar SPIFFS");
    return;
  }

  for (uint8_t t = 4; t > 0; t--)
  {
    Serial.printf("[SETUP] WAIT %d...\n", t);
    Serial.flush();
    delay(1000);
  }
  // Establecemos la velocidad de conexión por el puerto serie
  Serial.begin(115200);

  lcd.begin();
  lcd.backlight();
  LCD("CARGANDO", "ESPERE", 0);
  pinMode(coin, INPUT);
  pinMode(button, INPUT);
  pinMode(rest, INPUT);
  rest1 = digitalRead(rest);

  randomSeed(analogRead(A0));
  Serial.println("Cargando");
  // Ponemos a  ledPin  como salida
  pinMode(ledPin, OUTPUT);

  // Creamos una instancia de la clase WiFiManager
  mangerWifi();
  // WiFi.begin("TOTOLINK");
  WiFi.mode(WIFI_STA);

  Serial.println("Ya estás conectado");
  // Mientras no se conecte, mantenemos un bucle con reintentos sucesivos
  int e = 0;
  while (WiFi.status() != WL_CONNECTED)
  {
    delay(1000);
    // Esperamos un segundo
    Serial.println("Connecting to WiFi..");
    if (e == 0)
    {
      LCD("Conectando", "a WiFi", 0);
    }
    else if (e == 1)
    {
      LCD("Conectando", "..", 1);
    }
    else if (e == 2)
    {
      LCD("Conectando", "....", 1);
    }
    else if (e == 3)
    {
      LCD("Conectando", "......", 1);
      e = -1;
    }
    e++;
  }

  Serial.println();
  Serial.println(WiFi.SSID());
  Serial.print("IP address:\t");
  // Imprimimos la ip que le ha dado nuestro router
  Serial.println(WiFi.localIP());

  // Start server
  cargaRutas();
  LCD(WiFi.SSID(), WiFi.localIP().toString(), 0);
  delay(15000);

  server.begin();
}

//-------------------loop----------------------------
void loop()
{
  fisicos();
}
//------------------------fisicos---------------------
void fisicos()
{
  coin1 = digitalRead(coin);
  button1 = digitalRead(button);
  if (green == 0)
  {
    LCD("Bienvenido", "Inserte $5", 0);
    Serial.println("Bienvenido");
    green = 1;
  }
  else if (green == 1)
  {
    green = 1;
    if (coin1 != 0)
    {
      red = 1;
    }
    if (red == 1)
    {
      contador = 1 + contador;
      Serial.println(contador);
      int tiempo = contador * MIN;
      String timer;
      int horas, minun;
      horas = tiempo / 60;
      minun = tiempo % 60;

      if (horas <= 9)
      {
        timer = "0" + String(horas) + ":";
      }
      else
      {
        timer = String(horas) + ":";
      }

      if (minun <= 9)
      {
        timer = timer + "0" + String(minun) + ":00";
      }
      else
      {
        timer = timer + String(minun) + ":00";
      }

      LCD("Tu tiempo es: ", timer, blue);
      Serial.println("tu tiempo es: ");
      Serial.println(timer);
      minutos = "00:" + String(tiempo) + ":00";
      red = 0;
      blue = 1;
      delay(1500);
    }
    if (button1 != 0)
    {
      if (key == 2)
      {
        LCD("Gracias", "Nos vemos", 0);
        Serial.println("Gracias");
        red = 0;
        green = 0;
        contador = 0;
        key = 0;
      }
      else if (blue == 1 && contador != 0)
      {
        Serial.println("tu clave es: ");
        randomNumber = random(10000, 99999);
        char a = 64 + contador;
        String M = String(randomNumber);
        Serial.println(String(a) + M);
        clave = String(a) + M;
        LCD("Tu clave es: ", String(a) + M, 0);
        blue = 0;
        contador = 0;
        key = 2;
        get();
        delay(2000);
      }
    }
  }
}

//-------------------------metodoi de llenado de la pantalla------------------
void LCD(String a, String b, int c)
{
  if (c == 0)
  {
    lcd.clear();
  }
  lcd.setCursor(0, 0);
  lcd.print(a);
  lcd.setCursor(0, 1);
  lcd.print(b);
}

void get()
{
  WiFiClient client;
  HTTPClient http;
  Serial.print("[HTTP] begin...\n");
  // configure traged server and url
  http.begin(client, "http://" + ip + "/mikrotik/crear.php?ip=" + ipM + "&user=" + user + "&pass=" + pass + "&name=" + clave + "&limtime=" + minutos); // HTTP
  // http.addHeader("Content-Type", "application/x-www-form-inlencoded");
  Serial.println("http://" + ip + "/mikrotik/crear.php?ip=" + ipM + "&user=" + user + "&pass=" + pass + "&name=" + clave + "&limtime=" + minutos);
  // http.addHeader("Authorization", jwt);
  Serial.print("[HTTP] POST...\n");
  // start connection and send HTTP header and body
  int httpCode = http.GET();

  // httpCode will be negative on error
  if (httpCode > 0)
  {
    // HTTP header has been send and Server response header has been handled
    Serial.printf("[HTTP] POST... code: %d\n", httpCode);

    // file found at server
    if (httpCode == HTTP_CODE_OK)
    {
      const String &payload = http.getString();
      Serial.println("received payload:\n<<");
      Serial.println(payload);
      Serial.println(">>");
    }
    else
    {
      Serial.printf("[HTTP] POST... failed, error: %s\n", http.errorToString(httpCode).c_str());
    }
    http.end();
  }
}
