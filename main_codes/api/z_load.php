<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

$check = fastImageGet($_FILES["file"]["tmp_name"]);
if($check == false) {
  $response[0]=-1;
  $response[1]="NOT an Image or File size is too big...";  
  echo json_encode($response);
  exit;
}

if ($_FILES['file']['size'] > 6000000) {
  //echo "Sorry, your file is too large.";
  $response[0]=-1;
  $response[1]="Sorry, your file size: " . round($_FILES['file']['size']/1000000,2) . "MB is too large. Maximum is 6MB";
  echo json_encode($response);
  exit;
}

$file = $_FILES['file']['tmp_name'];
$dir=$_POST['dir'];
$sourceProperties = getimagesize($file);
//$ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
//$jfile = pathinfo($_FILES['file']['name'],PATHINFO_BASENAME);
$jfilename = pathinfo($_FILES['file']['name'],PATHINFO_FILENAME);
$dst=$dir.$jfilename.'.jpg';
//echo $sourceProperties[1];
//exit;

$imageType = $sourceProperties[2];
switch ($imageType) {
  case IMAGETYPE_PNG:    
      $imageResourceId = imagecreatefrompng($file); 
      $targetLayer = imageResize($imageResourceId,$sourceProperties[0],$sourceProperties[1]);        

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
      $targetLayer = imageResize($imageResourceId,$sourceProperties[0],$sourceProperties[1]);

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
      $targetLayer = imageResize($imageResourceId,$sourceProperties[0],$sourceProperties[1]);             
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
$response[0]=1;
$response[1]=" Image Updated Successfully.";
echo json_encode($response);

function imageResize($imageResourceId,$width,$height) {

    //obtain ratio
    $imageratio = $width/$height;
    $newwidth = 500;
    $newheight = 500 / $imageratio;
    $targetWidth=$newwidth;
    $targetHeight=$newheight;

  /*
  if($imageratio >= 1){
      $newwidth = 600;
      $newheight = 600 / $imageratio;
      $targetWidth=$newwidth;
      $targetHeight=$newheight;
  }
  else{
      $newwidth = 600;
      $newheight = 600 / $imageratio;
      $targetWidth=$newwidth;
      $targetHeight=$newheight;
  };
  */

  $targetLayer=imagecreatetruecolor($targetWidth,$targetHeight);
  imagecopyresampled($targetLayer,$imageResourceId,0,0,0,0,$targetWidth,$targetHeight, $width,$height);

  return $targetLayer;
}


//fastImageGet('image.jpg');         // returns size and image type in array or false if not image
//fastImageGet('image.jpg', 'type'); // returns image type only
//fastImageGet('image.jpg', 'size'); // returns image size only

function fastImageGet($file, $what=null) {

    if (!in_array($what, ['size', 'type']))
        $what = null;

    // INIT

    $pos = 0; $str = null;

    if (is_resource($file))
        $fp = $file;

    elseif (!@filesize($file))
        return false;

    else
        try {
            $fp = fopen($file, 'r', false);
        } catch (\Exception $e) {
            return false;
        }


    // HELPER FUNCTIONS

    $getChars = function($n) use (&$fp, &$pos, &$str) {
        $response = null;

        if (($pos + $n - 1) >= strlen($str)) {
            $end = $pos + $n;

            while ((strlen($str) < $end) && ($response !== false)) {
                $need = $end - ftell($fp);

                if (false !== ($response = fread($fp, $need)))
                    $str .= $response;
                else
                    return false;
            }
        }

        $result = substr($str, $pos, $n);
        $pos += $n;
        return $result;
    };

    $getByte = function() use ($getChars) {
        $c = $getChars(1);
        $b = unpack('C', $c);
        return reset($b);
    };

    $readInt = function ($str) {
        $size = unpack('C*', $str);
        return ($size[1] << 8) + $size[2];
    };


    // GET TYPE

    $t2 = $getChars(2);

    if ($t2 === 'BM')
        $type = 'bmp';
    elseif ($t2 === 'GI')
        $type = 'gif';
    elseif ($t2 === chr(0xFF) . chr(0xd8))
        $type = 'jpeg';
    elseif ($t2 === chr(0x89) . 'P')
        $type = 'png';
    else
        $type = false;

    if (($type === false) || ($what === 'type')) {
        fclose($fp);
        return $type;
    }


    // GET SIZE

    $pos = 0;

    if ($type === 'bmp') {
        $chars = $getChars(29);
        $chars = substr($chars, 14, 14);
        $ctype = unpack('C', $chars);
        $size = (reset($ctype) == 40)
            ? unpack('L*', substr($chars, 4))
            : unpack('L*', substr($chars, 4, 8));

    } elseif ($type === 'gif') {
        $chars = $getChars(11);
        $size = unpack('S*', substr($chars, 6, 4));

    } elseif ($type === 'jpeg') {
        $state = null;

        while (true) {

            switch ($state) {

                default:
                    $getChars(2);
                    $state = 'started';
                    break;

                case 'started':
                    $b = $getByte();
                    if ($b === false) {
                        $size = false;
                        break 2;
                    }
                    $state = $b == 0xFF ? 'sof' : 'started';
                    break;

                case 'sof':
                    $b = $getByte();

                    if (in_array($b, range(0xE0, 0xEF)))
                        $state = 'skipframe';

                    elseif (in_array($b, array_merge(range(0xC0, 0xC3), range(0xC5, 0xC7), range(0xC9, 0xCB), range(0xCD, 0xCF))))
                        $state = 'readsize';

                    elseif ($b == 0xFF)
                        $state = 'sof';

                    else
                        $state = 'skipframe';

                    break;

                case 'skipframe':
                    $skip = $readInt($getChars(2)) - 2;
                    $state = 'doskip';
                    break;

                case 'doskip':
                    $getChars($skip);
                    $state = 'started';
                    break;

                case 'readsize':
                    $c = $getChars(7);
                    $size = [$readInt(substr($c, 5, 2)), $readInt(substr($c, 3, 2))];
                    break 2;
            }
        }

    } elseif ($type === 'png') {
        $chars = $getChars(25);
        $size = unpack('N*', substr($chars, 16, 8));
    }


    // COMPLETE

    fclose($fp);

    if (is_array($size))
        $size = array_values($size);

    return ($what === 'size') ? $size : [$type, $size];
}

?>