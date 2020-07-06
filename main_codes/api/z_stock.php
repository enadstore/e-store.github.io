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

// get all records
if($request == 0){  	  
  echo json_encode(retAllData($clientno,'stock',''));
  exit;
}


// search record
if($request == 10){  
	$stmt = $DBcon->prepare("SELECT * from stock");
  $stmt->execute();
  while($rows=$stmt->FETCH(PDO::FETCH_ASSOC)) {
    $response[] = $rows;
  }
  echo json_encode($response);
  exit;
}

// Add record
if($request == 2){
  $stockno = $data->stockno;
  $stockname = $data->stockname;
  $descrp = $data->descrp;
  $photo = $data->photo;
  $orient = $data->orient;
  $price = $data->price;
  $catno = $data->catno;
  $cost=0;
  $bal=0;
  $promo=0;
  
  $sql="INSERT INTO `stock`(stockno,stockname,descrp,catno,photo,orient,price,cost,bal,promo,clientno)
                  VALUES (:stockno,:stockname,:descrp,:catno,:photo,:orient,:price,:cost,:bal,:promo,:clientno)";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array( ':stockno'  => $stockno,
                        ':descrp'  => $descrp,
                        ':stockname'  => $stockname,
                        ':catno'  => $catno,
                        ':photo'  => $photo,
                        ':orient'  => $orient,
                        ':price'  => $price,
                        ':cost'  => $cost,
                        ':bal'  => $bal,
                        ':promo'  => $promo,
                        ':clientno'  => $clientno
                        ));
  
  echo json_encode(retAllData($clientno,'stock',''));
  exit;
}

// Update record
if($request == 3){
  $stockno = $data->stockno;
 $stockname = $data->stockname;  
  $descrp = $data->descrp;
  $photo = $data->photo;
  $orient = $data->orient;
  $price = $data->price;
  $catno = $data->catno;  

  $sql="UPDATE stock SET stockname=:stockname,descrp=:descrp,catno=:catno,photo=:photo,orient=:orient,price=:price where stockno=:stockno";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array( 
                        ':stockname'  => $stockname,
                        ':descrp'  => $descrp,
                        ':catno'  => $catno,
                        ':photo'  => $photo,
                        ':orient'  => $orient,
                        ':price'  => $price,                        
                        ':stockno'  => $stockno));
                        
  echo json_encode(retAllData($clientno,'stock',''));
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
    $stmt = $DBcon->prepare("SELECT * from stock where promo <> 0 order by promo");
    $stmt->execute();
    while($rows=$stmt->FETCH(PDO::FETCH_ASSOC)) {      
      $stk = $rows['stockno'];
      $ctr++;
      $sql2="UPDATE stock SET promo=:ctr where stockno=:stockno";      
      $stmt2 = $DBcon->prepare($sql2);
      $stmt2->execute(array( ':ctr'  => $ctr,                    
                            ':stockno'  => $stk));                        
    }
  }

  echo json_encode(retAllData($clientno,'stock',''));
  exit;
}

// Delete record
if($request == 4){
  $stockno = $data->stockno;
  $photo = $data->photo;
  $dir = $data->dir;

  $sql="DELETE FROM stock WHERE stockno=:stockno and clientno=:clientno";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(':stockno'  => $stockno,':clientno'  => $clientno));
  if (file_exists($dir.$photo)) {
    unlink($dir.$photo);
  }  
  echo json_encode(retAllData($clientno,'stock',''));
  exit;
}
