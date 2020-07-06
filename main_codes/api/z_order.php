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

if($request == 0 || $request == 1){    
  $usercode = $data->usercode;
  
  $vval='';
  if($request==1){ $vval=$usercode; }

  $response[0]=retAllData($clientno,'orders',$vval);   
  $response[1]=retAllData($clientno,'orders2',$vval);   
  
  echo json_encode($response);   
  exit; 
}

// add record
if($request == 2){
  $trano = $data->trano; 
  $trandate = $data->trandate; 
  $trantime = $data->trantime; 
  $usercode = $data->usercode;   
  $stat=0;
  $unread=0;
  $ob=$data->ob; 
  
  //create orders2
  $orderTotal=0;

  for($i=0; $i < count($ob); $i++){    
    $stockno = $ob[$i]->stockno;

    $sql="INSERT INTO `orders2`(clientno,trano,trandate,trantime,usercode,stat,stockno,qty,price,amount)
      VALUES (:clientno,:trano,:trandate,:trantime,:usercode,:stat,:stockno,:qty,:price,:amount)";
    $stmt = $DBcon->prepare($sql);
    $stmt->execute(array(':clientno'  => $clientno,
                ':trano'    => $trano,
                ':trandate' => $trandate,
                ':trantime' => $trantime,
                ':usercode' => $usercode,
                ':stat'     => $stat,

                ':stockno'  => $stockno,
                ':qty'      => (int) $ob[$i]->qty,
                ':price'    => (float) $ob[$i]->price,
                ':amount'   => (float) $ob[$i]->amount
              ));

    $orderTotal=$orderTotal + (float) $ob[$i]->amount;
    //delete from cart ; stockno usercode
    $sql="DELETE FROM cart WHERE stockno=:stockno and usercode=:usercode and clientno=:clientno";
    $stmt = $DBcon->prepare($sql);
    $stmt->execute(array( ':clientno'  => $clientno,
                          ':stockno'  => $stockno,
                          ':usercode'  => $usercode
                        ));
  }

  //create orders main file
  $sql="INSERT INTO `orders`(clientno,usercode,stat,trano,trandate,trantime,amount,unread)
              VALUES (:clientno,:usercode,:stat,:trano,:trandate,:trantime,:amount,:unread)";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array( ':clientno'  => $clientno,
                        ':usercode'  => $usercode,
                        ':stat'  => $stat,                        
                        ':trano'  => $trano,
                        ':trandate'  => $trandate,
                        ':trantime'  => $trantime,
                        ':amount'  => $orderTotal,
                        ':unread'  => $unread
                      ));
  
  //update user d_active date
  $sql="UPDATE user SET d_active=:d_active where usercode=:usercode and clientno=:clientno";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array( ':clientno'  => $clientno,
                        ':usercode'  => $usercode,
                        ':d_active'  => $trandate
                      ));

  echo json_encode(retAllData($clientno,'orders',''));
  exit;
}

// Update record
if($request == 3){
  $usercode = $data->usercode;
  $trantype = $data->trantype;
  $trandate = $data->trandate;
  $descrp = $data->descrp;
  $stockno = $data->stockno;

  $sql="UPDATE orders SET descrp=:descrp where stockno=:stockno and clientno=:clientno";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(':descrp'  => $descrp,':stockno'  => $stockno, ':clientno'  => $clientno));
  echo "Order updated successfully";
  exit;
}

// Update record msg chat
if($request == 31){  
  $stockno = $data->stockno;
  $sender = $data->sender;
  $unread=1;
  $sql="UPDATE orders SET unread=:unread where stockno=:stockno and sender=:sender and unread=0 and clientno=:clientno";
  
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(':unread'  => $unread,':stockno'  => $stockno,':sender'  => $sender, ':clientno'  => $clientno));
  echo "Unread becomes Read";
  exit;
}
// Update order charges 
if($request == 32){  
  $trano = $data->trano;
  $charge = $data->charge;
  $total = $data->total;

  $sql="UPDATE orders SET charge=:charge,total=:total where trano=:trano and clientno=:clientno";  
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(
        ':charge'  => $charge,
        ':total'  => $total,
        ':trano'  => $trano,
        ':clientno'  => $clientno));
  echo json_encode(retAllData($clientno,'orders',''));
  exit;
}

// Update order status
if($request == 331){
  $usercode = $data->usercode;
  $trano = $data->trano;
  $stat = $data->stat;
  $unread=0;
  if($stat > 0) { $unread=0; }
  $sql="UPDATE orders SET stat=:stat where usercode=:usercode and trano=:trano and clientno=:clientno";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(
                  ':usercode'  => $usercode,
                  ':trano'  => $trano,
                  ':stat'  => $stat,
                  ':clientno'  => $clientno
                ));
                
  addNotif($trano,$usercode,$clientno,$stat);
  //-----------------------------------------------------------------------            
  echo json_encode(retAllData($clientno,'orders',''));
  exit;
}

// Delete record
if($request == 4){  
  $trano = $data->trano;
  $usercode = $data->usercode;
  $photo=$trano.'.jpg';

  $sql="DELETE FROM orders WHERE trano=:trano and clientno=:clientno";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(':trano' => $trano, ':clientno'  => $clientno));

  if (file_exists('upload/chat/'.$photo)) {
    unlink ('upload/chat/'.$photo);
  }
  echo json_encode(retAllData($clientno,'chat',$usercode));
  exit;
}

// Delete cancelled order
if($request == 41){  
  $trano = $data->trano;
  $usercode = $data->usercode;

  $sql="DELETE FROM orders WHERE trano=:trano and clientno=:clientno";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(':trano' => $trano, ':clientno'  => $clientno));

  $sql="DELETE FROM orders2 WHERE trano=:trano and clientno=:clientno";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(':trano' => $trano, ':clientno'  => $clientno));

  addNotif($trano,$usercode,$clientno,4);

  echo json_encode(retAllData($clientno,'orders',''));
  exit;
}


//add notification for the client
function addNotif($trano,$usercode,$clientno,$stat){
  include '../../dbcon/dbcon.php';
  $aryStat=array();
  $aryStat[0]="is being placed as an order.";
  $aryStat[1]="is now being prepared for delivery.";
  $aryStat[2]="is now in transit. Delivery is on your way.";
  $aryStat[3]="is marked as received item. Please acknowledge.";
  $aryStat[4]="is DELETED as Cancelled Item.";
  $msg='Your transaction (Ref: '.$trano.' '.$aryStat[$stat];
  $unread=0;
  //$no=$count->rowCount();
  $a_sql="SELECT * from notif where trano=:trano and usercode=:usercode and clientno=:clientno";
  $a_stmt = $DBcon->prepare($a_sql);
  $a_stmt->execute(array(':trano' => $trano,':usercode' => $usercode, ':clientno'  => $clientno));
  if($a_stmt->rowCount() > 0){
    $a_sql="UPDATE notif SET stat=:stat, msg=:msg, unread=:unread where trano=:trano and usercode=:usercode and clientno=:clientno";
  }else{
    $a_sql="INSERT INTO `notif`(trano,usercode,clientno,msg,stat,unread) VALUES (:trano,:usercode,:clientno,:msg,:stat,:unread)";
  }
  $a_stmt = $DBcon->prepare($a_sql);
  $a_stmt->execute(array(
            ':trano'  => $trano, 
            ':usercode'  => $usercode,                       
            ':clientno'  => $clientno,
            ':msg'  => $msg,
            ':stat'  => $stat,
            ':unread' => $unread            
            ));
}