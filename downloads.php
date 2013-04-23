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
	
	
	switch ($params[0]) {
		case 'news':
		case 'stat':
			header('Content-type: image/'.substr($ext, 1, 3));
			echo file_get_contents(ROOT . '/sys/files/'.$params[0].'/'.$params[1]);
			break;
		case 'loads':
			$attach = $FpsDB->select('loads_attaches', DB_FIRST, array('cond' => array('filename' => $params[1])));
			if (count($attach) < 1) die();
			header('Content-type: image/'.substr($ext, 1, strlen($ext) - 1));
			echo file_get_contents(ROOT . '/sys/files/'.$params[0].'/'.$params[1]);
			break;
		default:
			die();
	}
}

die();
