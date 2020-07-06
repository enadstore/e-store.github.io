<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

$response = array();

$data = json_decode(file_get_contents("php://input"));
$request = $data->request;
$clientno = $data->clientno;   
include '../../dbcon/dbcon.php';
include '../../main_codes/api/jbelib.php';

// Check of Online
if($request == 404){ 
  $response[0]="ONLINE";
  $response[1]=retData();
  echo json_encode($response);
}
// Fetch All records
if($request == 0){ 
  /*
  $stmt = $DBcon->prepare("SELECT * from sysfile");  
  $stmt->execute();
  while($rows=$stmt->FETCH(PDO::FETCH_ASSOC)) {
    $response[] = $rows;
  } 
  echo json_encode($response);
  */
  echo json_encode(retData());
  exit;
}
// Check a single record
if($request == 1){   
  $stmt = $DBcon->prepare("SELECT * from sysfile WHERE clientno=:clientno");  
  $stmt->execute(array(':clientno' => $clientno));  
  if($stmt->rowCount() > 0){
    echo "EXIST";
  }else{
    echo "";
  }
  exit;
}
// Add record
if($request == 2){
  $logoImg=$data->logoImg;  
  $f_icon=$data->f_icon;  

  $logo=$data->logo;  
  $banner="banner.jpg";

  $appname = $data->appname;
  $shortname = $data->shortname;
  $descrp = $data->descrp;
  $site = $data->site;
  $dir=$data->dir; 
  $sw_version=$data->sw_version;   

  $newDir='../../app/'.$site;    
  mkdir($newDir);

  // create master.txt
  //$myfile = fopen($newDir."/master.txt", "w") or die("Unable to open file!");
  //$txt = '[ { "clientno":"'.$clientno.'", "site":"'.$site.'" } ]';
  //fwrite($myfile, $txt);  
  //fclose($myfile);

  //create manifest file
  $myfile2 = fopen($newDir."/manifest.webmanifest", "w") or die("Unable to open file!");
  $txt2 =  
    '{
      "name": "'.$appname.'",
      "short_name": "'.$shortname.'",
      "start_url": ".",
      "display": "standalone",
      "orientation": "portrait",
      "background_color": "#a9a9a9",
      "theme_color": "#a9a9a9",
      "description": "'.$descrp.'",
      "icons": [
        {
          "src": "gfx/icon-512x512.png",
          "sizes": "512x512",
          "type": "image/png"
        },
        {
          "src": "gfx/icon-logo.png",
          "sizes": "192x192",
          "type": "image/png"
        }
      ]
    }';

  fwrite($myfile2, $txt2);  
  fclose($myfile2);
  
  // copy the files  
  full_copy("../../main_files",$newDir);
  //rename($newDir."/jstoreSW.js",$newDir."/SW_".substr($clientno,4).".js");
  rename($newDir."/jstoreSW.js",$newDir."/SW_".$clientno.".js");
  
  $hd1 = "< Promo Content >";
  $hd2 = "< Category Content >";
  $hd3 = "< Items Content >";
  $pg_title = "Title";
  $pg_body = "Body";
  $clor1 = "#0080ff";
  $clor2 = "#0080c0";
  $clor3 = "#0080ff";
  $clor4 = "#ff0000";

  $txclor1 = "#ffffff";
  $txclor2 = "#000000";
  $txclor3 = "#ffffff";
  $txclor4 = "#ffffff";
  
  $sql="INSERT INTO `sysfile`(logo,banner,clientno,clientname,appname,shortname,descrp,site,hd1,hd2,hd3,pg_title,pg_body,clor1,clor2,clor3,clor4,txclor1,txclor2,txclor3,txclor4)
    VALUES (:logo,:banner,:clientno,:clientname,:appname,:shortname,:descrp,:site,:hd1,:hd2,:hd3,:pg_title,:pg_body,:clor1,:clor2,:clor3,:clor4,:txclor1,:txclor2,:txclor3,:txclor4)";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(':logo'  => $logo,
              ':banner'  => $banner,
              ':clientno'  => $clientno,
              ':clientname'  => $appname,
              ':appname'  => $appname,
              ':shortname'  => $shortname,
              ':descrp'  => $descrp,
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

  //add new admin password for the new site
  $usercode = "A_".$clientno; 
  $userid = "admin"; 
  $pword = "admin"; 
  $username = "Administrator";
  $photo = $usercode.".jpg";
  $addrss = ""; 
  $celno = ""; 
  $usertype = 5;
  $fb='';
  $email='';
  $d_active = date('Y-m-d H:i:s');

  $sql="INSERT INTO `user`(usercode,userid,username,pword,usertype,photo,addrss,celno,fb,email,d_active,clientno)
      VALUES (:usercode,:userid,:username,:pword,:usertype,:photo,:addrss,:celno,:fb,:email,:d_active,:clientno)";
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
                        ':clientno' => $clientno
                        ));
  
  //copy("../../main_gfx/jadmin.jpg",$newDir.'/upload/users/'.$usercode.'.jpg');     
  //echo $newDir;
  //exit;
  //return;
  copy("../../main_gfx/jadmin.jpg",$newDir."/upload/users/".$usercode.".jpg");                          
  //********************************************************* */
  jeff_search_replace("<title>","</title>",$newDir."/index.html",$appname); //change title
  jeff_search_replace("var CURR_CLIENT='","';",$newDir."/index.html",$clientno); //new client number
  jeff_search_replace("var CURR_SITE='","';",$newDir."/index.html",$site); //new site

  saveImg(false,$logoImg,$logo,$dir);
  //saveImg(true,$bannerImg,$banner,$dir);
  //makeLogo($logoImg,$dir);
  if($f_icon){
      copy($logoImg,$dir."icon-logo.png");                          
  }
  echo json_encode(retData());
  exit;
}

// Update record
if($request == 3){
  $logo=$data->logo;  
  $f_icon=$data->f_icon;  
  $banner="banner.jpg";
  $appname = $data->appname;
  $shortname = $data->shortname;
  $descrp = $data->descrp;
  $site = $data->site;
  $logoImg=$data->logoImg;  
  $dir=$data->dir; 
  $sw_version=$data->sw_version; 
  
  $stmt = $DBcon->prepare("UPDATE sysfile SET logo=:logo,banner=:banner,appname=:appname, shortname=:shortname, descrp=:descrp where clientno=:clientno");
  $stmt->execute(array(
                        ':logo'  => $logo,
                        ':banner'  => $banner,
                        ':appname'  => $appname,
                        ':shortname'  => $shortname,
                        ':descrp'  => $descrp,
                        ':clientno'  => $clientno
                        ));
  
  jeff_search_replace("<title>","</title>","../../app/".$site."/index.html",$appname);
  // update service workers
  //$thefile="../../app/".$site."/SW_".$clientno.".js";
  //jeff_search_replace("cacheName = '","';",$thefile,$sw_version);
  update_SW($clientno, $site, $sw_version);
  //********************* */

  saveImg(false,$logoImg,$logo,$dir);
  //saveImg(false,$bannerImg,$banner,$dir);
  //makeLogo($logoImg,$dir);
  if($f_icon){
      copy($logoImg,$dir."icon-logo.png");                          
  }
    
  echo json_encode(retData());
  exit;
}

// Delete record
if($request == 4){
  $site=$data->site;  
  $sql="DELETE FROM sysfile WHERE clientno=:clientno";
  $stmt = $DBcon->prepare($sql);
  $stmt->execute(array(':clientno'  => $clientno));
  if (file_exists('../../app/'.$site)) {
    jdelete_directory('../../app/'.$site);
  }  
  // also delete all records
  delDB($clientno,'cart');
  delDB($clientno,'category');
  delDB($clientno,'chat');
  delDB($clientno,'comstock');
  delDB($clientno,'notif');
  delDB($clientno,'orders');
  delDB($clientno,'orders2');
  delDB($clientno,'stock');
  delDB($clientno,'user');
  //************************** */
  
  echo json_encode(retData());
  exit;
}

if($request == 5){
    $appname = $data->appname;
    $site = $data->site;
    $sw_version=$data->sw_version; 

    $newDir='../../app/'.$site; 
  
	  copy("../../main_files/index.html",$newDir."/index.html");          
    jeff_search_replace("<title>","</title>",$newDir."/index.html",$appname); //change title
    jeff_search_replace("var CURR_CLIENT='","';",$newDir."/index.html",$clientno); //new client number
    jeff_search_replace("var CURR_SITE='","';",$newDir."/index.html",$site); //new site          
    // update service worker  
    //$thefile=$newDir."/SW_".$clientno.".js";
    $thefile="../../app/".$site."/SW_".$clientno.".js";
    jeff_search_replace("cacheName = '","';",$thefile,$sw_version);
    //********************* */      
    echo json_encode(retData());
    exit;
}

function jsearch_replace($s1,$s2,$fle,$ilis){
  $all = file_get_contents($fle);
  $pos1=strpos($all,$s1); //first text search
  $pos2=strpos($all,$s2,$pos1);
  $len_s1=strlen($s1);
  if($pos1){
    $write=substr($all, 0, $pos1+$len_s1) . $ilis . substr($all, $pos2);
    file_put_contents($fle, $write);
  }
}

function full_copy( $source, $target ) {
  if ( is_dir( $source ) ) {
    @mkdir( $target );
    $d = dir( $source );
    while ( FALSE !== ( $entry = $d->read() ) ) {
      if ( $entry == '.' || $entry == '..' ) {
          continue;
      }
      $Entry = $source . '/' . $entry; 
      if ( is_dir( $Entry ) ) {
          full_copy( $Entry, $target . '/' . $entry );
          continue;
      }
      copy( $Entry, $target . '/' . $entry );
    }

    $d->close();
  }else {
    copy( $source, $target );
  }
}

function jdelete_directory($dirname) {
  if (is_dir($dirname))
    $dir_handle = opendir($dirname);
  if (!$dir_handle)
    return false;
  while($file = readdir($dir_handle)) {
    if ($file != "." && $file != "..") {
    if (!is_dir($dirname."/".$file))
        unlink($dirname."/".$file);
    else
        jdelete_directory($dirname.'/'.$file);
    }
  }
  closedir($dir_handle);
  rmdir($dirname);
  return true;
}

function retData(){
  include '../../dbcon/dbcon.php';
  $xresponse=array();
  $xsql="SELECT * from sysfile";  
  $xstmt = $DBcon->prepare($xsql);
  $xstmt->execute();
  while($xrows=$xstmt->FETCH(PDO::FETCH_ASSOC)) {     
    $xresponse[] = $xrows;    
  } 
  $xstmt=null;
  return $xresponse;
}


function delDB($clientno,$fle){
  include '../../dbcon/dbcon.php';
  $xresponse=array();
  $xsql="DELETE from ".$fle." where clientno=:clientno";  
  $xstmt = $DBcon->prepare($xsql);
  $xstmt->execute(array(':clientno' => $clientno));  
}

function getDB($clientno,$fle,$code){
  include '../../dbcon/dbcon.php';
  $xresponse=array();
  $xsql="SELECT * FROM ".$fle." where clientno=:clientno";  
  
  if($code == ''){    
    $xstmt = $DBcon->prepare($xsql);
    $xstmt->execute(array(':clientno' => $clientno));
  }else{    
    $xsql=$xsql." and usercode=:usercode";
    $xstmt = $DBcon->prepare($xsql);
    $xstmt->execute(array(':usercode' => $code, ':clientno' => $clientno));
  }
  while($xrows=$xstmt->FETCH(PDO::FETCH_ASSOC)) {     
    $xresponse[] = $xrows;    
  } 
  $xstmt=null;
  return $xresponse;
}
?>