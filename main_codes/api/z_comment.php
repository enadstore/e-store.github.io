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
// Fetch All records
if($request == 0){   
  echo json_encode(retAllData($clientno,'comstock',''));
  exit;
}

// Fetch All records
if($request == 1){
  echo json_encode(retAllData($clientno,'comstock',''));
  exit;
}

// Add record
if($request == 2){
  $stockno = $data->stockno;
  $comment = $data->comment;
  $usercode = $data->usercode;
  $username = $data->username;
  $sender=0;

  $sql="INSERT INTO `comstock`(stockno, comment, usercode, username,sender,clientno)
              VALUES (:stockno,:comment,:usercode,:username,:sender,:clientno)";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(':stockno'  => $stockno,                       
                       ':comment'  => $comment,
                       ':usercode'  => $usercode,
                       ':username'  => $username,
                       ':sender'  => $sender,
                       ':clientno'  => $clientno
                      ));		  
  echo json_encode(retAllData($clientno,'comstock',''));
  exit;
}

// Update record
if($request == 3){
  $usercode = $data->usercode;
  $trantype = $data->trantype;
  $trandate = $data->trandate;
  $descrp = $data->descrp;
  $trano = $data->trano;

  $sql="UPDATE chat SET descrp=:descrp where trano=:trano";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(':descrp'  => $descrp,':trano'  => $trano));
  echo "Order updated successfully";
  exit;
}

// Update record msg chat
if($request == 31){  
  $trano = $data->trano;
  $sender = $data->sender;
  $unread=1;
  $sql="UPDATE chat SET unread=:unread where trano=:trano and sender=:sender and unread=0";
  
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(':unread'  => $unread,':trano'  => $trano,':sender'  => $sender));
  echo "Unread becomes Read";
  exit;
}

// Delete record
if($request == 4){
  $id = $data->id;
  $sql="DELETE from comstock WHERE id=:id";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(':id' => $id));    
  echo json_encode(retAllData($clientno,'comstock',''));
  exit;
}

// Delete record
if($request == 44){
  $id = $data->id;
  $item = $data->item;

  $sql="DELETE from comstock WHERE id=:id";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(':id' => $id));
  if(!empty($item)){
    unlink ($item);
  }
  
  echo "Chat Item Deleted successfully";

  exit;
}
