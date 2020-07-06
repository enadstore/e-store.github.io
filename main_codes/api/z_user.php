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
  echo json_encode(retAllData($clientno,'user',''));
  exit;
}


// Fetch a record only 1
if($request == 1){  
  $usercode = $data->usercode; 
  echo json_encode(retAllData($clientno,'user',$usercode));
  exit;
}
if($request == 101){  
  $userid = $data->userid; 
  $pword = $data->pword; 
    
  $stmt = $DBcon->prepare("SELECT * from user WHERE userid=:userid and pword=:pword and clientno=:clientno");
  $stmt->execute(array(':userid'  => $userid,':pword'  => $pword,':clientno'  => $clientno));
  while($rows=$stmt->FETCH(PDO::FETCH_ASSOC)) {     
    $response[] = $rows;    
  } 
  echo json_encode($response);    
  exit;
}

// Add record
if($request == 2){ 
  $usercode = $data->usercode; 
  $userid = $data->userid; 
  $pword = $data->pword; 
  $username = $data->username; 
  $photo = $data->photo;
  $addrss = $data->addrss; 
  $celno = $data->celno; 
  $lat = $data->lat; 
  $lng = $data->lng; 
  $usertype = 0;
  $fb='';
  $email='';
  $d_active = date('Y-m-d H:i:s');

  $sql0="SELECT * from user WHERE clientno=:clientno and userid=:userid and pword=:pword";
  $stmt = $DBcon->prepare($sql0);
  $stmt->execute(array(':clientno'  => $clientno, ':userid'  => $userid, ':pword'  => $pword));		
  if($stmt->rowCount()){
    echo "EXIST";
  }else{
    $sql="INSERT INTO `user`(usercode,userid,username,pword,usertype,photo,addrss,celno,fb,email,d_active,lat,lng,clientno)
        VALUES (:usercode,:userid,:username,:pword,:usertype,:photo,:addrss,:celno,:fb,:email,:d_active,:lat,:lng,:clientno)";
    $stmt = $DBcon->prepare($sql);
    $stmt->execute(array( ':usercode'  => $usercode,
                          ':userid'  => $userid,
                          ':username'  => $username,
                          ':pword'  => $pword,
                          ':usertype'  => $usertype,
                          ':photo'  => $photo,
                          ':addrss'  => $addrss,
                          ':celno'  => $celno,
                          ':fb'  => $fb,
                          ':email'  => $email,                          
                          ':d_active'  => $d_active,
                          ':lat'  => $lat,
                          ':lng'  => $lng,
                          ':clientno' => $clientno
                          ));
    //echo "ADDED";
    echo json_encode(retAllData($clientno,'user',''));
  }
  exit;
}

// Update record
if($request == 3){
  $usercode = $data->usercode;
  $userid = $data->userid;
  $pword = $data->pword;
  $username = $data->username;
  $usertype = $data->usertype;
  $photo = $data->photo;
  $addrss = $data->addrss;
  $celno = $data->celno;
  $lat = $data->lat; 
  $lng = $data->lng; 
  $fb = '';
  $email='';
  
  $sql="UPDATE user SET usercode=:usercode,userid=:userid,username=:username,pword=:pword,usertype=:usertype,photo=:photo,addrss=:addrss,
      celno=:celno,fb=:fb,email=:email where usercode=:usercode and clientno=:clientno";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array( ':usercode'  => $usercode,
                        ':userid'  => $userid,
                        ':username'  => $username,
                        ':pword'  => $pword,
                        ':usertype'  => $usertype,
                        ':photo'  => $photo,
                        ':addrss'  => $addrss,
                        ':celno'  => $celno,
                        ':fb'  => $fb,
                        ':email'  => $email,          
                        ':clientno' => $clientno
                        ));
  echo json_encode(retAllData($clientno,'user',''));
  exit;
}
if($request == 301){
  $usercode = $data->usercode;
  $usertype = $data->usertype;
  
  $sql="UPDATE user SET usertype=:usertype where usercode=:usercode and clientno=:clientno";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array( ':usercode'  => $usercode,
                        ':usertype'  => $usertype,
                        ':clientno' => $clientno
                        ));
  echo json_encode(retAllData($clientno,'user',''));
  exit;
}
if($request == 302){
  $lat = $data->lat;
  $lng = $data->lng;
  $usercode = $data->usercode;
  $sql="UPDATE user SET lat=:lat, lng=:lng where usercode=:usercode and clientno=:clientno";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(
                        ':lat'  => $lat,
                        ':lng'  => $lng,
                        ':usercode'  => $usercode,                    
                        ':clientno' => $clientno
                        ));
  echo json_encode(retAllData($clientno,"user",""));
  exit;
}

// Delete record
if($request == 4){
  $usercode = $data->usercode; 
  $photo = $data->photo;
  $sql="DELETE FROM user WHERE usercode=:usercode and clientno=:clientno";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(':usercode' => $usercode,':clientno' => $clientno));
   
  //Delete user image  
  if (file_exists('upload/users/'.$photo)) {
    unlink('upload/users/'.$photo);
  }  
  echo json_encode(retAllData($clientno,'user',''));
  exit;
}