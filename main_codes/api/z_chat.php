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

// search record
if($request == 0){   
  echo json_encode(retAllData($clientno,'chat',''));
  exit;
}

// Fetch All records
if($request == 1){
  $usercode = $data->usercode;
  echo json_encode(retAllData($clientno,'chat',$usercode));
  exit;
}

// Add record
if($request == 2){
  $usercode = $data->usercode;
  $admin = $data->admin;
  $trano = $data->trano;
  $trantype = $data->trantype;
  $msg = $data->msg;
  $photo = $data->photo;
  $sender = $data->sender;
  $trandate = $data->trandate;
  $trantime = $data->trantime;

  //$xdate = str_replace('/', '-', $date);
  //$trandate = date('Y-m-d H:i:s');
  //$trandate=date('Y-m-d', strtotime($trandate));  

  //$expire_time = 'Mon Jun 23 2014 16:17:05 GMT+0530 (India Standard Time)';
  $expire_time = $trandate;
  //$expire_time = substr($expire_time, 0, strpos($expire_time, '('));
  //echo date('Y-m-d h:i:s', strtotime($expire_time));
  $trandate=date('Y-m-d h:i:s', strtotime($expire_time));

  $unread=0;

  $sql="INSERT INTO `chat`(clientno,trano, trantype, trandate, trantime, usercode, photo, msg,sender,admin,unread)
              VALUES (:clientno,:trano,:trantype,:trandate,:trantime,:usercode,:photo,:msg,:sender,:admin,:unread)";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(':clientno'  => $clientno,
                       ':trano'  => $trano,
                       ':trantype'  => $trantype,
                       ':trandate'  => $trandate,
                       ':trantime'  => $trantime,
                       ':usercode'  => $usercode,
                       ':photo'  => $photo,
                       ':msg'  => $msg,
                       ':sender'  => $sender,
                       ':admin'  => $admin,
                       ':unread'  => $unread
                      ));		
  //echo "Chat added successfully";
  echo json_encode(retAllData($clientno,'chat',$usercode));
  exit;
}

// Update record
if($request == 3){
  $usercode = $data->usercode;
  $trantype = $data->trantype;
  $trandate = $data->trandate;
  $descrp = $data->descrp;
  $stockno = $data->stockno;

  $sql="UPDATE chat SET descrp=:descrp where stockno=:stockno";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(':descrp'  => $descrp,':stockno'  => $stockno));
  echo "Order updated successfully";
  exit;
}

// Update record msg chat
if($request == 31){  
  $stockno = $data->stockno;
  $sender = $data->sender;
  $unread=1;
  $sql="UPDATE chat SET unread=:unread where stockno=:stockno and sender=:sender and unread=0";
  
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(':unread'  => $unread,':stockno'  => $stockno,':sender'  => $sender));
  echo "Unread becomes Read";
  exit;
}

// Delete record
if($request == 4){  
  $trano = $data->trano;
  $usercode = $data->usercode;
  $f_owner = $data->f_owner;
  $photo=$trano.'.jpg';

  $sql="DELETE FROM chat WHERE trano=:trano";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(':trano' => $trano));

  if (file_exists('upload/chat/'.$photo)) {
    unlink('upload/chat/'.$photo);
  }

  if($f_owner){
    echo json_encode(retAllData($clientno,'chat',''));
  }else{
    echo json_encode(retAllData($clientno,'chat',$usercode));
  }
  exit;
}

// mark as read
if($request == 5){  
  $usercode = $data->usercode;
  $sender = $data->sender;
  $unread=1;
  $sql="UPDATE chat SET unread=:unread where usercode=:usercode and sender=:sender and unread=0";
  
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(':unread'  => $unread,':usercode'  => $usercode,':sender'  => $sender));
  echo json_encode(retAllData($clientno,'chat',$usercode));
  exit;
}

// Fetch All uploads
if($request == 11){
  
  $userData = mysqli_query($con,"select * from uploads order by id desc");
  $response = array();
  while($row = mysqli_fetch_assoc($userData)){    
    $response[] = $row;
  }
  echo json_encode($response);
    
  exit;
}
