<?php
//*********************************************************************************************
function saveImg($keepRatio,$file,$flename,$site){
  $sourceProperties = getimagesize($file);

  $dst=$site.$flename;

  $imageType = $sourceProperties[2];

  switch ($imageType) {
  case IMAGETYPE_PNG:    
      $imageResourceId = imagecreatefrompng($file); 
      $targetLayer = jimageResize($keepRatio,$imageResourceId,$sourceProperties[0],$sourceProperties[1]);        

      $width  = imagesx($imageResourceId);
      $height = imagesy($imageResourceId);
      $colorRgb = array('red' => 255, 'green' => 255, 'blue' => 255);  //background color

      //create new image and fill with background color
      $backgroundImg = imagecreatetruecolor($width, $height);
      $color = imagecolorallocate($backgroundImg, $colorRgb['red'], $colorRgb['green'], $colorRgb['blue']);
      imagefill($backgroundImg, 0, 0, $color);
      imagecopy($backgroundImg, $imageResourceId, 0, 0, 0, 0, $width, $height);
      imagejpeg($backgroundImg,$dst);
      break;

  case IMAGETYPE_GIF:
      $imageResourceId = imagecreatefromgif($file); 
      $targetLayer = jimageResize($keepRatio,$imageResourceId,$sourceProperties[0],$sourceProperties[1]);

      $width  = imagesx($imageResourceId);
      $height = imagesy($imageResourceId);
      $colorRgb = array('red' => 255, 'green' => 255, 'blue' => 255);  //background color

      //create new image and fill with background color
      $backgroundImg = imagecreatetruecolor($width, $height);
      $color = imagecolorallocate($backgroundImg, $colorRgb['red'], $colorRgb['green'], $colorRgb['blue']);
      imagefill($backgroundImg, 0, 0, $color);
      imagecopy($backgroundImg, $imageResourceId, 0, 0, 0, 0, $width, $height);
      imagejpeg($backgroundImg,$dst);
      break;

  case IMAGETYPE_JPEG:
      $imageResourceId = imagecreatefromjpeg($file); 
      $targetLayer = jimageResize($keepRatio,$imageResourceId,$sourceProperties[0],$sourceProperties[1]);             
      imagejpeg($targetLayer,$dst);
      break;

  default:
      //echo "Invalid Image";
      $response[0]=-1;
      $response[1]="Invalid Image";
      exit;
      break;
  }

  imagedestroy($targetLayer);
}

function jimageResize($keepRatio,$imageResourceId,$width,$height) {
  //obtain ratio
  $imageratio = $width/$height;  
  if($keepRatio){
    $newwidth = $width;
    $newheight = $height;
  }else{
    $newwidth = 500;
    $newheight = 500 / $imageratio;
  }
  $targetWidth=$newwidth;
  $targetHeight=$newheight;

  $targetLayer=imagecreatetruecolor($targetWidth,$targetHeight);
  imagecopyresampled($targetLayer,$imageResourceId,0,0,0,0,$targetWidth,$targetHeight, $width,$height);
  return $targetLayer;
}

//*********************************************************************************************
//Your Image
function makeLogo($imgSrc,$site){
  //$imgSrc = "image.jpg";
  $dst=$site.'/icon-logo.png';

  //getting the image dimensions
  list($width, $height) = getimagesize($imgSrc);

  $sourceProperties = getimagesize($imgSrc);
  $imageType = $sourceProperties[2];

  switch ($imageType) {
  case IMAGETYPE_PNG:    
      $myImage = @imagecreatefrompng($imgSrc);
      break;

  case IMAGETYPE_GIF:
      $myImage = @imagecreatefromgif($imgSrc);
      break;

  case IMAGETYPE_JPEG:
      $myImage = @imagecreatefromjpeg($imgSrc);      
      break;

  default:
      //echo "Invalid Image";
      //$response[0]=-1;
      //$response[1]="Invalid Image";
      exit;
      break;
  }

  //saving the image into memory (for manipulation with GD Library)
  //$myImage = @imagecreatefromjpeg($imgSrc);
  if(!$myImage){ return; }

  // calculating the part of the image to use for thumbnail
  if ($width > $height) {
    $y = 0;
    $x = ($width - $height) / 2;
    $smallestSide = $height;
  } else {
    $x = 0;
    $y = ($height - $width) / 2;
    $smallestSide = $width;
  }

  // copying the part into thumbnail
  $thumbSize = 192;
  
  $thumb = imagecreatetruecolor($thumbSize, $thumbSize);  
  /*
    imagesavealpha($thumb, true);
    $trans_colour = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
    imagefill($thumb, 0, 0, $trans_colour);
    */
  imagecopyresampled($thumb, $myImage, 0, 0, $x, $y, $thumbSize, $thumbSize, $smallestSide, $smallestSide);

  //final output
  header('Content-type: image/png');
  imagejpeg($thumb,$dst);
}
//*********************************************************************************************

function retAllData($clientno,$fle,$code){
  include '../../dbcon/dbcon.php';
  $xresponse=array();
  $xsql="SELECT * from ".$fle." where clientno=:clientno";  
  
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

function jeff_search_replace($s1,$s2,$fle,$ilis){
  $all = file_get_contents($fle);
  $pos1=strpos($all,$s1); //first text search
  $pos2=strpos($all,$s2,$pos1);
  $len_s1=strlen($s1);
  if($pos1){
    $write=substr($all, 0, $pos1+$len_s1) . $ilis . substr($all, $pos2);
    file_put_contents($fle, $write);
  }
}
function  update_SW($clientno,$site,$sw_version){
  // update service worker  
  $thefile="../../app/".$site."/SW_".$clientno.".js";
  jeff_search_replace("cacheName = '","';",$thefile,$sw_version);
 }
?>