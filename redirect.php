<?php
if (empty($_GET['url'])) die();
$url = $_GET['url'];


function siteInList($site, $list) {
	if ($site && $list && is_array($list) && count($list) > 0) {
		foreach ($list as $item) {
			$pattern = '#^' . str_replace('*', '.*', str_replace('.', '\.', trim(mb_strtolower($item)))) . '$#i';
			if (preg_match($pattern, $site)) {
				return true;
			}
		}
	}
	
	return false;
}
	
	
include_once 'sys/boot.php';


$Register = Register::getInstance();


$whitelist = explode(',', $Register['Config']::read('whitelist_sites'));
$blacklist = explode(',', $Register['Config']::read('blacklist_sites'));

$redirect = Config::read('redirect_active');
$delay = Config::read('url_delay');

if (!$delay || $delay < 1) $delay = 10;

$in_white = false;
$in_black = false;


if ($redirect) {
	$info = parse_url($url);
	
	if (isset($info['host'])) {
		$site = trim(mb_strtolower($info['host']));
		$in_white = siteInList($site, $whitelist);
		$in_black = (!$in_white) ? siteInList($site, $blacklist) : false;
	}
}


if ($in_white) {
	header('Refresh: 0; url=' . $url);
	die();
} else {
	if (!$in_black) {
		header('Refresh: ' . $delay . '; url=' . $url);
		die();
	}
	
	$View = new Fps_Viewer_Manager();
	echo $View->view('redirect.html', array('url' => $url, 'black' => $in_black, 'template_path' => get_url('/template/' . $Register['Config']::read('template'))));
}



