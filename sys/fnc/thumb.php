<?php

function resampleImage($path, $new_path, $sizew, $sizeh = false) 
{
	if (false == $sizeh) $sizeh = $sizew;
	

	if (!isset($sizew) || $sizew < 150) $sizew = 150;
	if (!isset($sizeh) || $sizeh < 150) $sizeh = 150;

	$itype = 2;
	if (function_exists('exif_imagetype')) {
		$itype = exif_imagetype($path);
		switch ($itype) {
			case IMAGETYPE_JPEG: $img = imagecreatefromjpeg($path); break;
			case IMAGETYPE_GIF: $img = imagecreatefromgif($path); break;
			case IMAGETYPE_PNG: $img = imagecreatefrompng($path); break;
			case IMAGETYPE_BMP: $img = imagecreatefrombmp($path); break;
			default: return false;
		}
		if(!$img) return false;
	} else if (function_exists('getimagesize')) {
		@$info = getimagesize($path);
		if (!$info || empty($info['mime'])) return false;
		$itype = $info['mime'];
		switch ($itype) {
			case 'image/jpeg': $img = imagecreatefromjpeg($path); break;
			case 'image/gif': $img = imagecreatefromgif($path); break;
			case 'image/png': $img = imagecreatefrompng($path); break;
			case 'image/bmp': $img = imagecreatefrombmp($path); break;
			default: return false;
		}
	} else {
		$img = imagecreatefromjpeg($path);
	}
	$w = imagesx($img);
	$h = imagesy($img);
	if ($w < $sizew && $h < $sizeh) {
		$nw = $w;
		$nh = $h;
	} else {
		if (($w / $sizew) < ($h / $sizeh)) {
			$nw = intval($w * $sizeh / $h);
			$nh = $sizeh;
		} else {
			$nw = $sizew;
			$nh = intval($h * $sizew / $w);
		}
	}

	$dest = imagecreatetruecolor($nw, $nh);
	switch ($itype) {
		case 'image/gif':
		case 'image/png':
			imagecolortransparent($dest, imagecolortransparent($img));
			imagealphablending($dest, false);
			imagesavealpha($dest, true);
			break;
		default: break;
	}
	imagecopyresampled($dest, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);

	$quality_jpeg = Config::read('quality_jpeg');
	if (isset($quality_jpeg)) $quality_jpeg = (intval($quality_jpeg) < 0 || intval($quality_jpeg) > 100) ? 75 : intval($quality_jpeg);
	$quality_png = Config::read('quality_png');
	if (isset($quality_png)) $quality_png = (intval($quality_png) < 0 || intval($quality_png) > 9) ? 9 : intval($quality_png);

	switch ($itype) {
		case 1:
		case 'image/gif':
			imagegif($dest, $new_path);
			break;
		case 2:
		case 'image/jpeg':
			imagejpeg($dest, $new_path, $quality_jpeg);
			break;
		case 3:
		case 'image/png':
			imagepng($dest, $new_path, $quality_png);
			break;
		default:
			imagejpeg($dest, $new_path, $quality_jpeg);
			break;
	}
	imagedestroy($img);
	imagedestroy($dest);
	return true;
}

