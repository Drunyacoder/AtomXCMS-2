<?php
include_once 'sys/boot.php';
$allowed_ext = array('.png', '.gif', '.jpg', '.jpeg');


$Register = Register::getInstance();
$FpsDB = $Register['DB'];



$params = (!empty($_GET['url'])) ? explode('/', $_GET['url']) : array();
if (!empty($params[0]) && !empty($params[1])) {

	$ext = strchr($params[1], '.');
	$ext = strtolower($ext);
	if (!in_array($ext, $allowed_ext)) die();
	
	
	if ($params[0] == 'loads') {
		$attach = $FpsDB->select('loads_attaches', DB_FIRST, array('cond' => array('filename' => $params[1])));
		if (count($attach) < 1) die();
	}
	
	
	// Size of future image
	if (!empty($params[2])) {
		$sample_size = (int)$params[2];
	} else {
		$sample_size = $Register['Config']->read('img_preview_size');
	}
	
	// Min allowed size
	if ($sample_size < 50) $sample_size = 50;
	

	// New path
	$tmpdir = ROOT . '/sys/tmp/img_cache/' . $sample_size . '/' . $params[0] . '/';
	if (!file_exists($tmpdir)) mkdir($tmpdir, 0777, true);
	
	
	
	if (!file_exists($tmpdir . $params[1])) {
		$dest_path = ROOT . '/sys/files/'.$params[0].'/'.$params[1];
		resampleImage($dest_path, $tmpdir . $params[1], $sample_size);
	}
	
	
	header('Content-type: image/'.substr($ext, 1, 3));
	echo file_get_contents($tmpdir . $params[1]);
}

die();
