<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Access-Control-Allow-Methods, Access-Control-Allow-Headers, Allow, Access-Control-Allow-Origin");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, HEAD");
header("Allow: GET, POST, PUT, DELETE, OPTIONS, HEAD");
$headers = apache_request_headers(); //Obtenemos los headers de la petición

$ip = $_GET['ip'];
$user = $_GET['user'];
$pass = $_GET['pass'];
$id = $_GET['id'];

strval($ip);
strval($user);
strval($pass);
strval($id);

require('routeros_api.class.php');

$API = new RouterosAPI();

$API->debug = true;

if (!(empty($id))) {

    if ($API->connect($ip, $user, $pass)) {
        $r = array("crear"=>"y");
        echo json_encode($r); //enviamos el arreglo json
        header("http/1.1 201 ok"); //enviamos el código de estado 200 ok
        $API->comm('/ip/hotspot/user/remove', array(
            ".id" => $id
        ));
        
        //$READ = $API->read(false);
        $API->disconnect();
        //$ARRAY = $API->parseResponse($READ);
        
    }
}
