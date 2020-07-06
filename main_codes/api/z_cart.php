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
  echo json_encode(retAllData($clientno,'cart',''));
  exit;
}

// Fetch All records
if($request == 1){
  $usercode = $data->usercode;
  echo json_encode(retAllData($clientno,'cart',$usercode));
  exit;
}

// Add record
if($request == 2){
  $stockno = $data->stockno;
  $usercode = $data->usercode;
  $stat=0;

  $sql="SELECT * from cart WHERE stockno=:stockno and usercode=:usercode and clientno=:clientno";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(':stockno' => $stockno,':usercode' => $usercode,':clientno'  => $clientno));
  if($stmt->rowCount() > 0){
    echo "EXIST";
  }else{
    $sql="INSERT INTO `cart`(stockno,usercode,stat,clientno)
                    VALUES (:stockno,:usercode,:stat,:clientno)";
    $stmt = $DBcon->prepare($sql);
    $stmt->execute(array( ':stockno'  => $stockno,                        
                          ':usercode'  => $usercode,
                          ':stat'  => $stat,
                          ':clientno'  => $clientno
                          ));
    
    echo json_encode(retAllData($clientno,'cart',$usercode));
  }
  exit;
}

// Update record
if($request == 3){
  $stockno = $data->stockno;
  $descrp = $data->descrp;
  $photo = $data->photo;
  $orient = $data->orient;
  $price = $data->price;
  $catno = $data->catno;  

  $sql="UPDATE cart SET descrp=:descrp,catno=:catno,photo=:photo,orient=:orient,price=:price where stockno=:stockno and clientno=:clientno";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array( ':descrp'  => $descrp,
                        ':catno'  => $catno,
                        ':photo'  => $photo,
                        ':orient'  => $orient,
                        ':price'  => $price,                        
                        ':stockno'  => $stockno,
                        ':clientno'  => $clientno
                      ));
                        
  echo json_encode(retAllData($clientno,'cart',$usercode));
  exit;
}

// Update record
if($request == 30){
  $stockno = $data->stockno;
  $promo = $data->promo;

  $sql="UPDATE stock SET promo=:promo where stockno=:stockno and clientno=:clientno";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array( ':promo'  => $promo,                    
                        ':stockno'  => $stockno,
                        ':clientno'  => $clientno
                      ));                        
  if($promo==0){
    $ctr=0;
    $stmt = $DBcon->prepare("SELECT * from stock where promo <> 0 and clientno=:clientno order by promo");
    $stmt->execute();
    while($rows=$stmt->FETCH(PDO::FETCH_ASSOC)) {      
      $stk = $rows['stockno'];
      $ctr++;
      $sql2="UPDATE stock SET promo=:ctr where stockno=:stockno and clientno=:clientno";      
      $stmt2 = $DBcon->prepare($sql2);
      $stmt2->execute(array( ':ctr'  => $ctr,                    
                            ':stockno'  => $stk,
                            ':clientno'  => $clientno
                            ));                        
    }
  }
  
  echo json_encode(retAllData($clientno,'cart',$usercode));
  exit;
}

// Delete record
if($request == 4){
  $stockno = $data->stockno;
  $photo = $data->photo;

  $sql="DELETE FROM stock WHERE stockno=:stockno and clientno=:clientno";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(':stockno'  => $stockno,':clientno'  => $clientno));
  if (file_exists('upload/'.$photo)) {
    unlink('upload/'.$photo);
  }
  echo json_encode(retAllData($clientno,'cart',$usercode));
  exit;
}

// Delete record
if($request == 41){
  $usercode = $data->usercode;
  $stockno = $data->stockno;

  $sql="DELETE FROM cart WHERE usercode=:usercode and stockno=:stockno and clientno=:clientno";  
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(':usercode'  => $usercode, ':stockno'  => $stockno, ':clientno'  => $clientno));    
  echo json_encode(retAllData($clientno,'cart',$usercode));
  exit;
}

