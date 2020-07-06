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
  $stmt = $DBcon->prepare("SELECT * from sysfile");  
  $stmt->execute();
  while($rows=$stmt->FETCH(PDO::FETCH_ASSOC)) {
    $response[] = $rows;
  }  
  echo json_encode($response);
  exit;
}

// Fetch single record 
if($request == 1){  
  $res = array();
  $site=$data->site; 

  $stmt = $DBcon->prepare("SELECT * from sysfile where clientno=:clientno");  
  $stmt->execute(array(':clientno'  => $clientno));
  while($rows=$stmt->FETCH(PDO::FETCH_ASSOC)) {     
    $res[] = $rows;    
  }
  $response[0]=$res;

  //sliders==============================
  $dir_site="../../app/".$site."/gfx/";
  $out = array();
  foreach (glob($dir_site.'slide*.jpg') as $filename) {
    //$p = pathinfo($filename);
    //$out[] = $p['filename'];
      $p = pathinfo($filename,PATHINFO_BASENAME);      
      $out[] = $p;
  }
  $response[1]=$out;  
  echo json_encode($response);    
  exit;
}

// Add record
if($request == 2){
  $banner=$data->banner; 
  $clientname=$data->clientname;
  $site=$data->site; 
  $hd1 = $data->hd1;
  $hd2 = $data->hd2;
  $hd3 = $data->hd3;
  $pg_title = $data->pg_title;
  $pg_body = $data->pg_body;
  $clor1 = $data->clor1;
  $clor2 = $data->clor2;
  $clor3 = $data->clor3;
  $clor4 = $data->clor4;

  $txclor1 = $data->txclor1;
  $txclor2 = $data->txclor2;
  $txclor3 = $data->txclor3;
  $txclor4 = $data->txclor4;

  $sql="INSERT INTO `sysfile`(logo,banner,clientno,clientname,site,hd1,hd2,hd3,pg_title,pg_body,clor1,clor2,clor3,clor4,txclor1,txclor2,txclor3,txclor4)
              VALUES (:logo,:banner,:clientno,:clientname,:site,:hd1,:hd2,:hd3,:pg_title,:pg_body,:clor1,:clor2,:clor3,:clor4,:txclor1,:txclor2,:txclor3,:txclor4)";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(':logo'  => 'logo.jpg',
                        ':banner'  => $banner,
                        ':clientno'  => $clientno,
                        ':clientname'  => $clientname,
                        ':site'  => $site,
                        ':hd1'  => $hd1,
                        ':hd2'  => $hd2,
                        ':hd3'  => $hd3,
                        ':pg_title'  => $pg_title,
                        ':pg_body'  => $pg_body,
                        ':clor1'  => $clor1,
                        ':clor2'  => $clor2,
                        ':clor3'  => $clor3,
                        ':clor4'  => $clor4,
                        ':txclor1'  => $txclor1,
                        ':txclor2'  => $txclor2,
                        ':txclor3'  => $txclor3,
                        ':txclor4'  => $txclor4
                        ));
 
  echo json_encode(retAllData($clientno,'sysfile',''));
  exit;
}

// Update record
if($request == 3){    
  $keepRatio=$data->keepRatio;
  $bannerImg=$data->bannerImg;
  $arySlides=$data->arySlides;    
  $site=$data->site;
  $clientname=$data->clientname;
  
  $hd1 = $data->hd1;

  $hd2 = $data->hd2;
  $hd3 = $data->hd3;
  $pg_title = $data->pg_title;
  $pg_body = $data->pg_body;  
  $clor1 = $data->clor1;

  $clor2 = $data->clor2;
  $clor3 = $data->clor3;
  $clor4 = $data->clor4;
  $txclor1 = $data->txclor1;
  $txclor2 = $data->txclor2;

  $txclor3 = $data->txclor3;
  $txclor4 = $data->txclor4;
  $sw_version=$data->sw_version;
  
  //echo count($banner);
  $dtl='';
  $dir_site="../../app/".$site."/gfx/";
  $ndxFle="../../app/".$site."/index.html";
  jeff_search_replace('<meta name="theme-color" content="','"/>',$ndxFle,$clor1);
  update_SW($clientno, $site, $sw_version);
  //save banner
  saveImg($keepRatio,$bannerImg,"banner.jpg",$dir_site);

  //save slides
  for($i=0;$i<count($arySlides);$i++){
    $id=$arySlides[$i]->id;
    $img=$arySlides[$i]->img;
    saveImg($keepRatio,$img,"slide".($id+1).".jpg",$dir_site);
  }
  
  
  
  $stmt = $DBcon->prepare("UPDATE sysfile SET clientname=:clientname,hd1=:hd1,
                            hd2=:hd2,hd3=:hd3,pg_title=:pg_title,pg_body=:pg_body,clor1=:clor1,                            
                            clor2=:clor2,clor3=:clor3,clor4=:clor4,txclor1=:txclor1,txclor2=:txclor2,
                            txclor3=:txclor3,txclor4=:txclor4 where clientno=:clientno");
                            
  $stmt->execute(array(                   
                        ':clientname'  => $clientname,                     
                        ':hd1'  => $hd1,
                        ':hd2'  => $hd2,
                        ':hd3'  => $hd3,
                        ':pg_title'  => $pg_title,
                        ':pg_body'  => $pg_body,
                        ':clor1'  => $clor1,

                        ':clor2'  => $clor2,
                        ':clor3'  => $clor3,
                        ':clor4'  => $clor4,
                        ':txclor1'  => $txclor1,
                        ':txclor2'  => $txclor2,

                        ':txclor3'  => $txclor3,
                        ':txclor4'  => $txclor4,

                        ':clientno'  => $clientno
                        ));
  
  echo json_encode(retAllData($clientno,'sysfile',''));
  exit; 
}

