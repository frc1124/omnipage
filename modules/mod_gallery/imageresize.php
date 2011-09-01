<?
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

include "../../includes/common.php";

$md5 = md5($_GET["src"].$_GET["type"].$_GET["width"].$_GET["height"]);

if(isset($_GET["cache"])){
	echo $root_path."/media/cache/".$md5;
	exit;
	}

if(file_exists($root_path."/media/cache/".$md5)){
	header('Content-Type: image/jpeg');
	echo file_get_contents($root_path."/media/cache/".$md5);
	exit;
}

$source = $root_path."/media/photos/".$_GET["src"];

ob_start();

switch ($_GET["type"]) {
	case "albumThumbnail":
		$base = imagecreatefromjpeg($root_path."/skins/classic/images/albumBase.jpg");
		$second = imagecreatefromjpeg($source);
		list($width, $height) = getimagesize($source);
		imagecopyresampled($second,$second,0,0,0,0,75,57,$width,$height);
		imagecopy($base,$second,24,26,0,0,76,57);
		header('Content-Type: image/jpeg');
		imagejpeg($base,NULL,80);
		imagedestroy($base);
		imagedestroy($second);
		break;
	case "thumbnail":
		$base = imagecreatetruecolor(119,119);
		$background = imagecolorallocate($base,255,255,219);
		imagefill($base,0,0,$background);
		$second = imagecreatefromjpeg($source);
		list($width, $height) = getimagesize($source);
		
		//if width > height
		if($width>$height){
			$height2=119/$width*$height;
			imagecopyresampled($base,$second,0,(119-$height2)/2,0,0,119,$height2,$width,$height);
		}
		
		//if width < height
		if($height>$width){
			$width2=119/$height*$width; 
			imagecopyresampled($base,$second,(119-$width2)/2,0,0,0,$width2,119,$width,$height);
		}
		
		header('Content-Type: image/jpeg');
		imagejpeg($base,NULL,80);
		imagedestroy($base);
		imagedestroy($second);
		break;
	case "resize":
		$second = imagecreatefromjpeg($source);
		list($width, $height) = getimagesize($source);
		
		if(is_numeric($_GET["width"])){
			$newWidth = min($_GET["width"],2000);
			$newHeight = $height * $newWidth / $width; 
			}
		
		else{
			if(is_numeric($_GET["height"])){
				$newHeight = min($_GET["height"],2000);
				$newWidth = $width * $newHeight / $height; 
			}
			else{
					if($width>$height){
						$newHeight=349/$width*$height;
						$newWidth=349;
					}
					if($height>$width){
						$newWidth=349/$height*$width;
						$newHeight=349;
					}
				}
			}

		$base = imagecreatetruecolor($newWidth,$newHeight);
		imagecopyresampled($base,$second,0,0,0,0,$newWidth,$newHeight,$width,$height);
		
		header('Content-Type: image/jpeg');
		imagejpeg($base,NULL,60);
		imagedestroy($base);
		imagedestroy($second);
		break;
	}

$file = fopen($root_path."/media/cache/".$md5,"w");
fwrite($file,ob_get_contents());
fclose($file);

ob_end_flush();

?>