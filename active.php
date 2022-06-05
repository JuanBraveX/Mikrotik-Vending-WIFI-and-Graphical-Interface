<?php
require('routeros_api.class.php');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Access-Control-Allow-Methods, Access-Control-Allow-Headers, Allow, Access-Control-Allow-Origin");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, HEAD");
header("Allow: GET, POST, PUT, DELETE, OPTIONS, HEAD");
$headers = apache_request_headers(); //Obtenemos los headers de la petición
$metodo = $_SERVER['REQUEST_METHOD']; //Obtenemos el método de la petición

$API = new RouterosAPI();
$API->debug = true;

if ($metodo === 'GET') {
   if (isset($_GET['pass']) && isset($_GET['pass']) && isset($_GET['pass'])) {
      if ($API->connect(strval($_GET['ip']), strval($_GET['user']), strval($_GET['pass']))) {
         $ARRAY = $API->comm('/ip/hotspot/active/print');
            $r = [];
            foreach ($ARRAY as $item) { //recorremos el array contenedor
               $nuevoArreglo = []; //creamos un nuevo arreglo
               if (isset($item['server'])) { //si existe la clave server         
                  foreach ($item as $key => $value) { //recorremos cada array del array contenedor
                     if ($key === '.id' || $key === 'user' || $key === 'limit-uptime' || $key === 'uptime') { // si la clave es igual a .id, name, limit-uptime o uptime
                        if ($key === 'limit-uptime') {
                           $nuevoArreglo["limituptime"] = $value;
                        } else {
                           $nuevoArreglo[$key] = $value; //agregamos al nuevo arreglo la clave y el valor
                        }
                     }
                  }
                  $r[] = $nuevoArreglo; //agregamos el nuevo arreglo al arreglo principal
               }
            }
            header("http/1.1 200 ok"); //enviamos el código de estado 200 ok
            echo json_encode($r); //enviamos el arreglo json
         $API->disconnect();
      } else {
         $r = array("login" => "n", "token" => "Error de usuario/contraseña");
         echo json_encode($r);
      }
   } else {
      $r = array("login" => "n", "token" => "Error wey");
      echo json_encode($r);
   }
} else {
   $r = array("login" => "n", "token" => "Error GET");
   echo json_encode($r);
}