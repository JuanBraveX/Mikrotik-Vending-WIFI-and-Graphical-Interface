   
<?php
require('routeros_api.class.php');

$headers = apache_request_headers(); //Obtenemos los headers de la petición
$metodo = $_SERVER['REQUEST_METHOD']; //Obtenemos el método de la petición

$ip = $_GET['fip'];
$user = $_GET['fuser'];
$pass = $_GET['fpass'];
//$ip = file_get_contents('https://api.ipify.org');
/*
exec("arp -a", $output, $returnValue);
foreach ($output as $line) {
   echo $line . '<br>';
}
*/
strval($ip);
strval($user);
strval($pass);

$API = new RouterosAPI();
$API->debug = true;

if ($API->connect($ip, $user, $pass)) {
   $ARRAY = $API->comm('/ip/hotspot/user/print');

   if ($metodo == 'GET') {
      $r = [];
      foreach ($ARRAY as $item) { //recorremos el array contenedor
         $nuevoArreglo = []; //creamos un nuevo arreglo
         if (isset($item['server'])) { //si existe la clave server         
            foreach ($item as $key => $value) { //recorremos cada array del array contenedor
               if ($key === '.id' || $key === 'name' || $key === 'limit-uptime' || $key === 'uptime') { // si la clave es igual a .id, name, limit-uptime o uptime
                  $nuevoArreglo[$key] = $value; //agregamos al nuevo arreglo la clave y el valor
               }
            }
            $r[] = $nuevoArreglo; //agregamos el nuevo arreglo al arreglo principal
         }
      }
      header("http/1.1 200 ok"); //enviamos el código de estado 200 ok
      echo json_encode($r); //enviamos el arreglo json
   }

   $API->disconnect();
}


?>