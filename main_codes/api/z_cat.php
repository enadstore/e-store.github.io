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
  echo json_encode(retAllData($clientno,'category',''));
  exit;
}

// Add record
if($request == 2){
  $catno = $data->catno;
  $descrp = $data->descrp;
  $photo = $data->photo;
  $orient = $data->orient;
  $bal=0;

  $sql="INSERT INTO `category`(catno, descrp, photo, orient, bal, clientno)
              VALUES (:catno,:descrp,:photo,:orient,:bal,:clientno)";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(':catno'  => $catno,
                       ':descrp'  => $descrp,
                       ':photo'  => $photo,
                       ':orient'  => $orient,
                       ':bal'  => $bal,
                       ':clientno'  => $clientno
                      ));		

  echo json_encode(retAllData($clientno,'category',''));
  exit;
}

// Update record
if($request == 3){  
  $photo = $data->photo;
  $orient = $data->orient;
  $descrp = $data->descrp;
  $catno = $data->catno;

  $sql="UPDATE category SET descrp=:descrp,photo=:photo,orient=:orient where catno=:catno";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array( ':descrp'  => $descrp,
                        ':photo'  => $photo,
                        ':orient'  => $orient,  
                        ':catno'  => $catno));  
  echo json_encode(retAllData($clientno,'category',''));
  exit;
}

// Delete record
if($request == 4){
  $catno = $data->catno;
  $photo = $data->photo;
  $dir = $data->dir;
  $sql="DELETE FROM category WHERE catno=:catno and clientno=:clientno";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(':catno' => $catno,':clientno'  => $clientno));
   
  //Delete all stock with same catno
  $sql="DELETE FROM stock WHERE catno=:catno and clientno=:clientno";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(':catno' => $catno, ':clientno'  => $clientno));

  if (file_exists($dir.$photo)) {
    unlink($dir.$photo);
  }  

  echo json_encode(retAllData($clientno,'category',''));
  exit;
}
