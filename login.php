<?php
require('routeros_api.class.php');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Access-Control-Allow-Methods, Access-Control-Allow-Headers, Allow, Access-Control-Allow-Origin, Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, HEAD");
header("Allow: GET, POST, PUT, DELETE, OPTIONS, HEAD");

require_once "jwt.php";
require_once "config.php";
$API = new RouterosAPI();
$API->debug = true;
$metodo = $_SERVER["REQUEST_METHOD"];

if ($metodo === 'GET') {
   if (isset($_GET['ip']) && isset($_GET['user']) && isset($_GET['pass'])) {
      if ($API->connect(strval($_GET['ip']), strval($_GET['user']), strval($_GET['pass']))) {
         // $API->connect($_GET['ip'], $_GET['user'], $_GET['pass'])
         $jwt = JWT::create(array("user"=>$_GET['user']),Config::SECRET);
         $r = array("login"=>"y", "token"=>$jwt);
         echo json_encode($r); //enviamos el arreglo json
         header("http/1.1 201 ok"); //enviamos el código de estado 200 ok
      

         $API->disconnect();
      } else {
         $r = array("login"=>"n", "token"=>"Error de usuario/contraseña");
         echo json_encode($r);
      }
   }else {
      $r = array("login"=>"n", "token"=>"Error");
      echo json_encode($r);
   }
}else {
   $r = array("login"=>"n", "token"=>"Error GET");
   echo json_encode($r);
}