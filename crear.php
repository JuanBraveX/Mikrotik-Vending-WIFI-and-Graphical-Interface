<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Access-Control-Allow-Methods, Access-Control-Allow-Headers, Allow, Access-Control-Allow-Origin");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, HEAD");
header("Allow: GET, POST, PUT, DELETE, OPTIONS, HEAD");
$headers = apache_request_headers(); //Obtenemos los headers de la petición

exec("arp -a ", $output);
$addres = [];
$ip;
$mac;
$contador = 0;
foreach ($output as $line) {
    $ip = substr($line, 0, 14);
    $mac = substr($line, -30, -9);
    if ($contador > 2) {
        if (substr($line, -4) == "mico") {
            $addres[] = array("ip" => $ip, "mac" => $mac);
        }
    }
    $contador++;
}


$ip = $_GET['ip'];
$user = $_GET['user'];
$pass = $_GET['pass'];
$name = $_GET['name'];
$limtime = $_GET['limtime'];

strval($ip);
strval($name);
strval($limtime);
strval($user);
strval($pass);

require('routeros_api.class.php');


$API = new RouterosAPI();
$API->debug = true;

$metodo = $_SERVER["REQUEST_METHOD"];
if ($metodo === 'GET') {
    if (isset($_GET['ip']) && isset($_GET['user']) && isset($_GET['pass']) && isset($_GET['name']) && isset($_GET['limtime'])) {
        if ($API->connect($ip, $user, $pass)) {
            $r = array("crear" => "y");
            echo json_encode($r); //enviamos el arreglo json
            header("http/1.1 201 ok"); //enviamos el código de estado 200 ok
            $API->comm('/ip/hotspot/user/add', array(
                "name"          =>  $name,
                "limit-uptime"  =>  $limtime,
                "server"        =>  "hotspot1"
            ));

            //$READ = $API->read(false);
            $API->disconnect();
            //$ARRAY = $API->parseResponse($READ);

        } else {
            $r = array("crear" => "n");
            echo json_encode($r); //enviamos el arreglo json
        }
    } else {
        $r = array("crear" => "n");
        echo json_encode($r); //enviamos el arreglo json
    }
}
