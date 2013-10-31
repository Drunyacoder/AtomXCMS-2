<?php
@ini_set('display_errors', 0);
sleep(2);

$out = '';

if (ini_get('safe_mod') == 1) {
	$out .= '<span style="color:#FF0000">safe_mod</span> - Возможности сервера ограничены<br />';
}

if (!function_exists('set_time_limit')) {
	$out .= '<span style="color:#FF0000">set_time_limit()</span> - Понадобится при быстром росте сайта<br />';
}


if (!function_exists('chmod')) {
	$out .= '<span style="color:#FF0000">chmod()</span> - Необходимо для смены прав на файлы и папки<br />';
}


if (!function_exists('getImageSize')) {
	$out .= '<span style="color:#FF0000">getImageSize()</span> - Необходимо для обработки изображений<br />';
}

if (!function_exists('imageCreateFromString')) {
	$out .= '<span style="color:#FF0000">imageCreateFromString()</span> - Необходимо для обработки изображений<br />';
}

if (!function_exists('imagecreatetruecolor')) {
	$out .= '<span style="color:#FF0000">imagecreatetruecolor()</span> - Необходимо для обработки изображений<br />';
}

if (!function_exists('imageCopy')) {
	$out .= '<span style="color:#FF0000">imageCopy()</span> - Необходимо для обработки изображений<br />';
}

if (!function_exists('imageGIF')) {
	$out .= '<span style="color:#FF0000">imageGIF()</span> - Необходимо для обработки изображений<br />';
}

if (!function_exists('imageJPEG')) {
	$out .= '<span style="color:#FF0000">imageJPEG()</span> - Необходимо для обработки изображений<br />';
}

if (!function_exists('imagePNG')) {
	$out .= '<span style="color:#FF0000">imagePNG()</span> - Необходимо для обработки изображений<br />';
}

if (!function_exists('imagecreatefromjpeg')) {
	$out .= '<span style="color:#FF0000">imagecreatefromjpeg()</span> - Необходимо для обработки изображений<br />';
}

if (!function_exists('imagecreatefromgif')) {
	$out .= '<span style="color:#FF0000">imagecreatefromgif()</span> - Необходимо для обработки изображений<br />';
}

if (!function_exists('imagecreatefrompng')) {
	$out .= '<span style="color:#FF0000">imagecreatefrompng()</span> - Необходимо для обработки изображений<br />';
}

if (!function_exists('imagesx')) {
	$out .= '<span style="color:#FF0000">imagesx()</span> - Необходимо для обработки изображений<br />';
}

if (!function_exists('imagesy')) {
	$out .= '<span style="color:#FF0000">imagesy()</span> - Необходимо для обработки изображений<br />';
}

if (!function_exists('imageDestroy')) {
	$out .= '<span style="color:#FF0000">imageDestroy()</span> - Необходимо для обработки изображений<br />';
}

if (!function_exists('exif_imagetype')) {
	$out .= '<span style="color:#FF0000">exif_imagetype()</span> - Необходимо для обработки изображений<br />';
}

if (!function_exists('imagecopyresampled')) {
	$out .= '<span style="color:#FF0000">imagecopyresampled()</span> - Необходимо для обработки изображений<br />';
}

if (!function_exists('imagecolorsforindex')) {
	$out .= '<span style="color:#FF0000">imagecolorsforindex()</span> - Необходимо для обработки изображений<br />';
}

if (!function_exists('imagecolorat')) {
	$out .= '<span style="color:#FF0000">imagecolorat()</span> - Необходимо для обработки изображений<br />';
}

if (!function_exists('imagesetpixel')) {
	$out .= '<span style="color:#FF0000">imagesetpixel()</span> - Необходимо для обработки изображений<br />';
}

if (!function_exists('imagecolorclosest')) {
	$out .= '<span style="color:#FF0000">imagecolorclosest()</span> - Необходимо для обработки изображений<br />';
}

if (empty($out)) echo '<span style="color:#46B100">Ваш сервер настроен идеально! :)</span><br />';
else echo $out;
?>






