<?php
/*---------------------------------------------\
|											   |
| Author:       Andrey Brykin (Drunya)         |
| Version:      1.7.2                          |
| Project:      CMS                            |
| package       CMS Fapos                      |
| subpackege    Helpers library                |
| copyright     ©Andrey Brykin 2010-2013       |
| last mod.     2013/02/22                     |
|----------------------------------------------|
|											   |
| any partial or not partial extension         |
| CMS Fapos,without the consent of the         |
| author, is illegal                           |
|----------------------------------------------|
| Любое распространение                        |
| CMS Fapos или ее частей,                     |
| без согласия автора, является не законным    |
\---------------------------------------------*/



/**
 *
 */
function show_date($date) {
	$Register = Register::getInstance();
	$timestamp = strtotime($date);
	
	if (!empty($_SESSION['user']) && !empty($_SESSION['user']['timezone'])) {
		if ($_SESSION['user']['timezone'] >= -12 && $_SESSION['user']['timezone'] <= 12)
		$timestamp = $timestamp + intval($_SESSION['user']['timezone']) * 60 * 60;
	}

	
	$format = $Register['Config']->read('date_format');
	$format = (!empty($format)) ? $format : 'Y-m-d H-i-s'; 
	return date($format, $timestamp);
}



/**
 * Format file size from bytes to K|M|G
 *
 * @param int $size
 * @return string - simple size with letter
 */
function getSimpleFileSize($size) {
	$size = intval($size);
	if (empty($size)) return '0 B';
	
	$ext = array('B', 'KB', 'MB', 'GB');
	$i = 0;
	
	while (($size / 1024) > 1) {
		$size = $size / 1024;
		$i++;
	}

	
	$size = round($size, 2) . ' ' . $ext[$i];
	return $size;
}



/**
 * Return count registered users. 
 * Cache results.
 */
function getAllUsersCount() {
	$Register = Register::getInstance();
    $FpsDB = $Register['DB'];

    $Cache = new Cache;
	$Cache->lifeTime = 3600;
	$Cache->prefix = 'statistic';
	
	if ($Cache->check('cnt_registered_users')) {
		$cnt = $Cache->read('cnt_registered_users');
	} else {
		$cnt = $FpsDB->select('users', DB_COUNT);
		$Cache->write($cnt, 'cnt_registered_users', array());
	}
	
	unset($Cache);
	return (!empty($cnt)) ? intval($cnt) : 0;
}

/**
 * Clean getAllUsersCount() cache
 */
function cleanAllUsersCount() {
	$Cache = new Cache;
	$Cache->lifeTime = 3600;
	$Cache->prefix = 'statistic';
	if ($Cache->check('cnt_registered_users')) {
		$Cache->remove('cnt_registered_users');
	}
}


/** 
 * Get users that born today 
 */
function getBornTodayUsers() {
	$Register = Register::getInstance();
    $FpsDB = $Register['DB'];
	$file = ROOT . '/sys/logs/today_born.dat';
	

	if (!file_exists($file) || (filemtime($file) + 3600) < time()) {
		$today_born = $FpsDB->select('users', DB_ALL, array(
			'cond' => array(
				"concat(`bmonth`,`bday`) ='".date("nj")."'",
			),
		));
		file_put_contents($file, serialize($today_born));
	} else {
		$today_born = file_get_contents($file);
		if (!empty($today_born)) $today_born = unserialize($today_born);
	}
	
	if (count($today_born) < 1) return array();
	return $today_born;
}


/**
 * Function for safe and get referer
 */
function setReferer() {
	if (!empty($_SERVER['HTTP_REFERER']) 
	&& preg_match('#^http://([^/]+)/(.+)#', $_SERVER['HTTP_REFERER'], $match)) {
		if (!empty($match[1]) && !empty($match[2]) && $match[1] == $_SERVER['SERVER_NAME']) {
			$_SESSION['redirect_to'] = $match[2];
		}
	}
}
function getReferer() {
	$redirect_to = get_url('/');
	
	if (isset($_SESSION['redirect_to'])) {
		$redirect_to = get_url('/' . $_SESSION['redirect_to']);
		unset($_SESSION['redirect_to']);
		
	} else if (!empty($_SERVER['HTTP_REFERER']) 
	&& preg_match('#^http://([^/]+)/(.+)#', $_SERVER['HTTP_REFERER'], $match)) {
		if (!empty($match[1]) && !empty($match[2]) && $match[1] == $_SERVER['SERVER_NAME']) {
			$redirect_to = get_url('/' . $match[2]);
		}
	}
	
	return $redirect_to;
}



/**
 * Get age from params
 *
 * @param int $y - year
 * @param int $m - month
 * @param int $d - day
 * @return int
 */
function getAge($y = 1970, $m = 1, $d = 1) {
	$y = intval($y); $m = intval($m); $d = intval($d);
	
	if ($y < 1970 || $y > 2010) $y = 1970;
	if ($m < 1 || $m > 12) $m = 1;
	if ($d < 1 || $d > 31) $d = 1;
	$m = str_pad($m, 2, 0, STR_PAD_LEFT);
	$d = str_pad($d, 2, 0, STR_PAD_LEFT);
	
	$btime = mktime(0, 0, 0, $m, $d, $y);
	$age_in_sec = time() - $btime;
	$age = floor($age_in_sec / 31536000);
	
	return $age;
}




/**
 * Check and return order param
 */
function getOrderParam($claas_name) {
	$order = (!empty($_GET['order'])) ? trim($_GET['order']) : '';
	
	switch ($claas_name) {
		case 'FotoModule':
		case 'StatModule':
		case 'NewsModule':
			$allowed_keys = array('views', 'date', 'comments');
			$default_key = 'date';
			break;
		case 'LoadsModule':
			$allowed_keys = array('views', 'date', 'comments', 'downloads');
			$default_key = 'date';
			break;
		case 'UsersModule':
			$allowed_keys = array('puttime', 'last_visit', 'name', 'rating', 'posts', 'status', 'warnings', 'city', 'jabber', 'byear', 'pol');
			$default_key = 'puttime';
			break;
	}
	
	if (empty($order) && empty($default_key)) return false;
	else if (empty($order) && !empty($default_key)) $out = $default_key;
	else {
		if (!empty($allowed_keys) && in_array($order, $allowed_keys)) {
			$out = $order;
		} else {
			$out = $default_key;
		}
	}
	
	return (!empty($_GET['asc'])) ? $out . ' ASC' : $out . ' DESC';
}
 


/**
 * CRON simulyation
 */
function fpsCron($func, $interval) {
	$cron_file = ROOT . '/sys/tmp/' . md5($func) . '_cron.dat';
	if (file_exists($cron_file)) {
		$extime = file_get_contents($cron_file);
		if (!empty($extime) && is_numeric($extime) && $extime > time()) {
			return;
		}
	}

	if (function_exists($func)) {
		file_put_contents($cron_file, (time() + intval($interval)));
		call_user_func($func);
	}
}
 
 
 
 
/**
 * Launch auto sitemap generator
 */
function createSitemap() {
	include_once ROOT . '/sys/inc/sitemap.class.php';
	$obj = new FpsSitemapGen;
	$obj->createMap();
}
 
 


/**
 * Only create HLU URL by title
 *
 * @param stirng title
 * @return string
 */
function getHluUrlByTitle($title) {
	$title = translit($title);
	$title = strtolower(preg_replace('#[^a-z0-9]#i', '_', $title));
	$hlu_extention = Config::read('hlu_extention');
	return $title . $hlu_extention;
}



/**
 * Create human like URL.
 * Get title of material and create url
 * from this title. OR create simple URL, if hlu is off.
 * 
 * @param array $materila
 * @param string $module
 * @return string 
 */
function entryUrl($material, $module) {
	$matId = $material->getId();
	$matTitle = $material->getTitle();
	

	if (empty($matId)) 
		trigger_error('Empty material ID', E_USER_ERROR);
		
	if (Config::read('hlu') != 1 || empty($matTitle)) {
		$url = $module . '/view/' . $matId;
		return $url;
	}
	
	// extention
	$extention = '';
	$hlu_extention = Config::read('hlu_extention');
	if (!empty($hlu_extention)) {
		$extention = $hlu_extention;
	}
	
	// URL pattern
	$pattern = '/' . $module . '/%s' . $extention;
	
	
	// Check tmp file with assocciations and build human like URL
	clearstatcache();
	$tmp_dir = ROOT . '/sys/tmp/hlu_' . $module . '/';
	$tmp_file = $tmp_dir . $matId . '.dat';
	touchDir($tmp_dir, 0777);
	if (file_exists($tmp_file) && is_readable($tmp_file)) {
		$title = file_get_contents($tmp_file);
		if (!empty($title)) {			
		
			if (!file_exists($tmp_dir . $title . '.dat')) {
				file_put_contents($tmp_dir . $title . '.dat', $matId);
			}
			return h(sprintf($pattern, $title));
		}
	}
	
	
	$title = translit($matTitle);
	$title = strtolower(preg_replace('#[^a-z0-9]#i', '_', $title));
	
	// Colission protect
	$tmp_file_title = $tmp_dir . $title . '.dat';
	while (file_exists($tmp_file_title)) {
		$collision = file_get_contents($tmp_file_title);
		if (!empty($collision) && $collision != $matId) {
			$title .= '_';
			$tmp_file_title = $tmp_dir . $title . '.dat';
		}
	}

	
	file_put_contents($tmp_file, $title);
	file_put_contents($tmp_dir . $title . '.dat', $matId);
	return h(sprintf($pattern, $title));
}



/**
 * Translit. Convert cirilic chars to 
 * latinic chars.
 *
 * @param string $str
 * @return string 
 */
function translit($str) {
	$cirilic = array('й', 'ц', 'у', 'к', 'е', 'н', 'г', 'ш', 'щ', 'з', 'х', 'ъ', 'ф', 'ы', 'в', 'а', 'п', 'р', 'о', 'л', 'д', 'ж', 'э', 'я', 'ч', 'с', 'м', 'и', 'т', 'ь', 'б', 'ю', 'ё', 'Й', 'Ц', 'У', 'К', 'Е', 'Н', 'Г', 'Ш', 'Щ', 'З', 'Х', 'Ъ', 'Ф', 'Ы', 'В', 'А', 'П', 'Р', 'О', 'Л', 'Д', 'Ж', 'Э', 'Я', 'Ч', 'С', 'М', 'И', 'Т', 'Ь', 'Б', 'Ю', 'Ё');
	$latinic = array('i', 'c', 'u', 'k', 'e', 'n', 'g', 'sh', 'sh', 'z', 'h', '', 'f', 'y', 'v', 'a', 'p', 'r', 'o', 'l', 'd', 'j', 'e', 'ya', 'ch', 's', 'm', 'i', 't', '', 'b', 'yu', 'yo', 'i', 'c', 'u', 'k', 'e', 'n', 'g', 'sh', 'sh', 'z', 'h', '', 'f', 'y', 'v', 'a', 'p', 'r', 'o', 'l', 'd', 'j', 'e', 'ya', 'ch', 's', 'm', 'i', 't', '', 'b', 'yu', 'yo');
	
	return str_replace($cirilic, $latinic, $str);
}




/**
 * Create captcha input field and image with 
 * security code.
 *
 */
function getCaptcha() {
	$kcaptcha = '/sys/inc/kcaptcha/kc.php?' . session_name() . '=' . session_id() . '&' . rand(rand(0, 1000), 999999);
	$tpl = file_get_contents(ROOT . '/template/' . Config::read('template') . '/html/default/captcha.html');
	return str_replace('{CAPTCHA}', $kcaptcha, $tpl);
}



/**
 * Work for language pack.
 * Open language file and return needed
 * string.
 *
 * @param int $key
 * @param string $module
 * @return string
 */
function __($key, $module = false) {
	
	if (is_numeric($key)) {
		if ($module === false) $module = 'common';
		
		$lan_dir = ROOT . '/sys/settings/languages/' . $module . '.dat';
		$data = file_get_contents($lan_dir);
		if (empty($data)) trigger_error('Language file not found!', E_USER_ERROR);
		$data = explode("=|=", $data);
		return (!empty($data[$key])) ? trim($data[$key]) : '';
		
	} else {
		$language = Config::read('language');
		if (empty($language) || !is_string($language)) $language = 'russian';
		$lang_file = ROOT . '/sys/settings/languages/' . $language . '.php';
		if (!file_exists($lang_file)) trigger_error('Language file not found', E_USER_ERROR);
		
		include $lang_file;
		if (array_key_exists($key, $language)) return $language[$key];
		return $key;
	}
}




/**
 * Uses for valid create HTML tag IMG
 * and fill into him correctli url.
 * When you use this function you
 * mustn't wory obout Fapos install
 * into subdri or SUBDIRS.
 * ALso if we wont change class of IMG or etc,
 * we change this only here and this changes apply
 * for evrywhere.
 *
 * @param string $url
 * @param array $params
 * @param boolean $notRoot
 * @return string HTML link
 */
function get_img($url, $params = array(), $notRoot = false) {
	$additional = '';
	if (!empty($params) && is_array($params)) {
		foreach($params as $key => $value) {
			$additional .= h($key) . '="' . h($value) . '" ';
		}
	}
	return '<img  ' . $additional . 'src="' . get_url($url, $notRoot) . '" />';
}


/**
 * Uses for valid create url.
 * When you use this function you
 * mustn't wory obout Fapos install
 * into subdri or SUBDIRS.
 * This function return url only from root (/)
 * But you can send $notRoot and get url from not root.
 *
 * @param string $url
 * @param boolean $notRoot
 * @return string url
 */
function get_url($url, $notRoot = false) 
{
	if ($notRoot) return Pather::parseRoutes($url);
	$url = '/' . WWW_ROOT . $url;
	// May be collizions
	$url = str_replace('//', '/', $url);
	return Pather::parseRoutes($url);
}



/**
 * Uses for valid create HTML tag A
 * and fill into him correctli url.
 * When you use this function you
 * mustn't wory obout Fapos install
 * into subdri or SUBDIRS.
 * ALso if we wont change class of A or etc,
 * we change this only here and this changes apply
 * for evrywhere.
 *
 * @param string $ankor
 * @param string $url
 * @param array $params
 * @param boolean $notRoot
 * @return string HTML link
 */
function get_link($ankor, $url, $params = array(), $notRoot = false) {
	$additional = '';
	if (!empty($params) && is_array($params)) {
		foreach($params as $key => $value) {
			$additional .= h($key) . '="' . h($value) . '" ';
		}
	}
	$link = '<a ' . $additional . 'href="' . get_url($url, $notRoot) . '">' . $ankor . '</a>';
	return $link;
}


/**
 * doing hard redirect
 * Send header and if header do not
 * work stop script and die. Better redirect
 * user to another page but if can't doing this
 * better stop script.
 */
function redirect($url, $header = 302) {
	
	$allowed_headers = array(301, 302);
	if (!in_array($header, $allowed_headers)) $header = 301;


	header('Location: ' . get_url($url), TRUE, $header);
	// :)
	die() or exit();
}



function createOptionsFromParams($offset, $limit, $selected = false) {
	$output = '';
	for ($i = $offset; $i <= $limit; $i++) {
		$select = ($selected !== false && $i == $selected) ? ' selected="selected"' : '';
		$output .= '<option value="' . $i . '"' . $select . '>' . $i . '</option>';
	}
	return $output;
}



/*
* return who online
* also we have analog in statistic module
*/
function getWhoOnline() {
	$path = ROOT . '/sys/logs/counter_online/online.dat';
	$users = 0;
	$quests = 0;
	$all = 0;
	
	if (file_exists($path) && is_readable($path)) {
		$data = unserialize(file_get_contents($path));
		$users = count($data['users']);
		$quests = count($data['guests']);
		$all = ($quests + $users);
	}
	
	return array('users' => $users, 'guests' => $quests, 'all' => $all);
}


/**
 * Return online users list
 */
function getOnlineUsers() {
	$path = ROOT . '/sys/logs/counter_online/online.dat';
	
	if (file_exists($path) && is_readable($path)) {
		$data = unserialize(file_get_contents($path));
		$users = $data['users'];
	}
	
	return $users;
}


/**
 * Get overal stats by key
 */
function getOveralStat($key = false) {
	$path = ROOT . '/sys/logs/overal_stats.dat';
	
	if (file_exists($path) && is_readable($path)) {
		$data = unserialize(file_get_contents($path));
		if (!empty($key)) {
			return (isset($data[$key])) ? $data[$key] : false;
		}
		return $data;
	}
	
	return false;	
}


/**
* touch and create dir
*/
function touchDir($path, $chmod = 0777) {
	if (!file_exists($path)) {
		mkdir($path, $chmod, true);
		chmod($path, $chmod);
	}
	return true;
}


/**
* print visibility param value
* @param string or array
*/
function pr($param) {
	echo '<pre>' . print_r($param, true) . '</pre>';
}


/**
* short version "htmlspecialchars()"
* @param string or array
*/
function h($param) {

	if (!is_array($param)) {
		$param = htmlspecialchars($param);
		$symbols = array(
			'&#125;' => '&amp;#125;',
			'&#123;' => '&amp;#123;',
		);
		return str_replace($symbols, array_keys($symbols), $param);
	}
	
	if (is_array($param)) {
		foreach ($param as $key => $value) {
			$param[$key] = h($value);
		}
		
		return $param;
	}
	
	return false;
}


/**
* @return timestamp on microseconds
*/
function getMicroTime() { 
    list($usec, $sec) = explode(" ", microtime()); 
    return ((float)$usec + (float)$sec); 
} 


/**
* for tests and dumps
*/
function dumpVar($var) {
	$f = fopen(ROOT . '/dump.dat', 'a+');
	fwrite($f, $var . "\n");
	fclose($f);
}


/**
 * mysql_real_escape_string copy
 */
function resc($str) {
	return mysql_real_escape_string($str);
}



function strips(&$param) {
	if (is_array($param)) {
		foreach($param as $k=>$v) {
			strips($param[$k]);
		}
	} else {
		$param = stripslashes($param);
		//$param = utf8Filter($param);
	}
}

/**
* cut all variables that not UTF-8
*/
function utf8Filter($str) {
	if (!preg_match('#.{1}#us', $str)) return '';
	else return $str;
}



function _unlink($path) {
	if (is_dir($path)) {
		$files = glob(rtrim($path, '/\\') . '/*');
		if (is_array($files) && count($files)) {
			foreach ($files as $file) {
				_unlink($file);
			}
		}
		rmdir($path);
	} else if (is_file($path)) {
		unlink($path);
	}
}


function memoryUsage($base_memory_usage) {
    printf("Bytes diff: %s<br />\n", getSimpleFileSize(memory_get_usage() - $base_memory_usage));
}