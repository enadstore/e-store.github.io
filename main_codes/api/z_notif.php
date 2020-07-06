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

// Fetch one records
if($request == 0){    
  $usercode = $data->usercode;
  $response[0]=retAllData($clientno,'cart',$usercode); 
  $response[1]=retAllData($clientno,'chat',$usercode); 
  $response[2]=retAllData($clientno,'notif',$usercode);   
  echo json_encode($response);   
  exit; 
}

// Update record
if($request == 301){    
  $trano = $data->trano;
  $usercode = $data->usercode;
  $unread = 1;

  $sql="UPDATE notif SET unread=:unread where trano=:trano and usercode=:usercode and clientno=:clientno";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(':unread'  => $unread,':trano'  => $trano, ':usercode'  => $usercode, ':clientno'  => $clientno));
  //echo "UPDATED";
  echo json_encode(retAllData($clientno,'notif',$usercode));
  exit;
}

// Delete record
if($request == 4){  
  $trano = $data->trano;
  $usercode = $data->usercode;
  $photo=$trano.'.jpg';

  $sql="DELETE FROM notif WHERE trano=:trano and usercode=:usercode and clientno=:clientno";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(':trano' => $trano, ':usercode' => $usercode, ':clientno'  => $clientno));
  echo json_encode(retAllData($clientno,'notif',$usercode));
  exit;
}

