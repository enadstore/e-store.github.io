<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

$response = array();

$data = json_decode(file_get_contents("php://input"));
$request = $data->request;
$clientno = $data->clientno;     
include '../../dbcon/dbcon.php';
include 'jbelib.php';

if($request == 0){    
  echo json_encode(retAllData($clientno,'notif','')); 
  exit;
}

// Fetch a record only if exist
if($request == 1){
  $usercode = $data->usercode;     
  echo json_encode(retAllData($clientno,'notif',$usercode)); 
  exit;
}
