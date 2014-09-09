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
 * Get one or couple entities.
 * If get one entity of the UsersModel, we also get user statistic
 *
 * @param $modelName
 * @param array $id
 * @return array
 * @throws Exception
 */
function fetch($modelName, $id = array()){
	$Register = Register::getInstance();
    try {
	    $model = $Register['ModManager']->getModelInstance($modelName);

        // get collection of entities
        if (is_array($id) && count($id)) {

            $id = array_map(function($n){
                $n = intval($n);
                if ($n < 1) throw new Exception('Only integer value might send as ID.');
                return $n;
            }, $id);
            $ids = implode(", ", $id);
            $result = $model->getCollection(array("`id` IN ($ids)"));

        // get one entity
        } else if (is_numeric($id)) {
            $id = intval($id);
            if ($id < 1) throw new Exception('Only integer value might send as ID.');
            $result = $model->getById($id);

            if ($result && strtolower($modelName) == 'users') {
                $stat = $model->getFullUserStatistic($id);
                $result->setStatistic($stat);
            }
        }

    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }

	return (!empty($result)) ? $result : array();
}


/**
 * Template operator for date formating
 *
 * @param $date
 * @param string $format
 * @return string
 */
function AtmGetDate($date, $format = false) {
	return AtmDateTime::getDate($date, $format);
}

function AtmGetSimpleDate($date) {
	return AtmDateTime::getSimpleDate($date);
}


/**
 * @param $errors
 * @return string
 */
function wrap_errors($errors) {
	return '<p class="errorMsg">' . __('Some error in form') . '</p>' .
			"\n" . '<ul class="errorMsg">' . "\n" . $errors . '</ul>' . "\n";
}



/**
 * Alias for file_get_contents
 */
function get_cont($path) {
	return file_get_contents($path);
}


	
/**
 * Replace image marker
 */
function insertImageAttach($entity, $announce, $module = null) {
	// replace image tags in text
	$attaches = $entity->getAttaches();
	if (!empty($attaches) && count($attaches) > 0) {
		$attachDir = ROOT . '/sys/files/' . $module . '/';
		foreach ($attaches as $attach) {
			if ($attach->getIs_image() == 1 && file_exists($attachDir . $attach->getFilename())) {
			
				$announce = str_replace('{IMAGE' . $attach->getAttach_number() . '}'
					, '<a class="gallery" href="' . get_url('/sys/files/' . $module . '/' . $attach->getFilename()) 
					. '"><img alt="' . h($entity->getTitle()) . '" title="' . h($entity->getTitle()) 
					. '" title="" src="' . get_url('/image/' . $module . '/' . $attach->getFilename()) . '" /></a>'
					, $announce);
			}
		}
	}

	return $announce;
}



function checkAccess($params = null) {
	if (isset($params) && is_array($params)) {
		$Register = Register::getInstance();
		return $Register['ACL']->turn($params, false);
	}
	return false;
}



function getAvatar($id_user = null, $email_user = null) {
	$def = get_url('/template/' . getTemplateName() . '/img/noavatar.png', false, false);
	
	if (isset($id_user) && $id_user > 0) {
		if (is_file(ROOT . '/sys/avatars/' . $id_user . '.jpg')) {
			return get_url('/sys/avatars/' . $id_user . '.jpg', false, false);
		} else {
			if (Config::read('use_gravatar', 'users') && function_exists('getGravatar')) {
				if (!isset($email_user)) {
					$Register = Register::getInstance();
					$usersModel = $Register['ModManager']->getModelInstance('Users');
					$user = $usersModel->getById($id_user);
					if ($user) {
						$email_user = $user->getEmail();
					} else {
						return $def;
					}
				}
				return getGravatar($email_user);
			} else {
				return $def;
			}
		}
	} else {
		return $def;
	}
}


/**
 * Get either a Gravatar URL or complete image tag for a specified email address.
 *
 * @param string $email The email address
 * @param string $s Size in pixels, defaults to 80px [ 1 - 2048 ]
 * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
 * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
 * @return String containing either just a URL or a complete image tag
 */
function getGravatar($email, $s = 120, $d = 'mm', $r = 'g') {
	$url = 'http://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . ".png?s=$s&d=$d&r=$r";
	return $url;
}


/**
* Get correct name of template for current user
*/
function getTemplateName()
{
	$template = Config::read('template');
	$template = Plugins::intercept('select_template', $template);
	return $template;
}



function getOrderLink($params) {
	if (!$params || !is_array($params) || count($params) < 2) return '';
	$order = (!empty($_GET['order'])) ? strtolower(trim($_GET['order'])) : '';
	$new_order = strtolower($params[0]);
	$active = ($order === $new_order);
	$asc = ($active && isset($_GET['asc']));


    $url = $_SERVER['REQUEST_URI'];
    $url = preg_replace('#(order=[^&]*[&]?)|(asc=[^&]*[&]?)#i', '', $url);
    if (substr($url, -1) !== '&' && substr($url, -1) !== '?') {
        $url .= (!strstr($url, '?')) ? '?' : '&';
    }

	return '<a href="' . $url . 'order=' . $new_order . ($asc ? '' : '&asc=1') . '">' . $params[1] . ($active ? ' ' . ($asc ? '↑' : '↓') : '') . '</a>';
}




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
 * Create human like URL.
 * Get title of material and create url
 * from this title. OR create simple URL, if hlu is off.
 * 
 * @param array $materila
 * @param string $module
 * @return string 
 */
function entryUrl($material, $module) {
	$Register = Register::getInstance();
	return $Register['URL']->getEntryUrl($material, $module);
}


function array_search_recursive($needle, $array) {
    $result = array();
	if (!is_array($array)) return $result;
    array_walk_recursive($array, function($v, $k) use ($needle, &$result){
        if ($needle === $k) array_push($result, $v);
    });
    return $result;
}



/**
 * Work for language pack.
 * Open language file and return needed
 * string.
 *
 * @param string $key
 * @param string $context
 * @return string
 */
function __($key, $context = false) {
    $Register = Register::getInstance();
    $language = getLang();
    if (empty($language) || !is_string($language)) $language = 'rus';
    if (!empty($Register['translation_cache']))
        $lang = $Register['translation_cache'];
    else {
        $lang_file = ROOT . '/sys/settings/languages/' . $language . '.php';
        $tpl_lang_file = ROOT . '/template/' . getTemplateName() .'/languages/' . $language . '.php';
        if (!file_exists($lang_file)) throw new Exception('Main language file not found');

        $lang = include $lang_file;
        if (file_exists($tpl_lang_file)) {
            $tpl_lang = include $tpl_lang_file;
            $lang = array_merge($lang, $tpl_lang);
        }


        $mod_langs = array_search_recursive($language, $Register['modules_translations']);
        if ($mod_langs) {
            foreach ($mod_langs as $path) {
                $mod_lang = include $path;
                $lang = array_merge($lang, $mod_lang);
            }
        }
        $Register['translation_cache'] = $lang;
    }


    if ($context && is_string($context)) {
        if (array_key_exists($context, $lang) && array_key_exists($key, $lang[$context])) {
            return $lang[$context][$key];
        }
    }
    if (array_key_exists($key, $lang)) return $lang[$key];
    return $key;
}


/**
 * Get the current user language
 */
function getLang() {
	return (!empty($_SESSION['lang'])) 
		? $_SESSION['lang']
		: Config::read('language');
}


/**
 * Get the permitted languages
 */
function getPermittedLangs() {
	$langs = Config::read('permitted_languages');
	if (!empty($langs)) {
		$langs = array_filter(explode(',', $langs));
		$langs = array_map(function($n){
			return trim($n);
		}, $langs);
		return $langs;
	} else {
		$lang_files = glob(ROOT . '/sys/settings/languages/*.php');
		$langs = array();
		if (!empty($lang_files)) {
			foreach($lang_files as $lang_file) {
				$lang = substr(substr(strrchr($lang_file, '/'), 1), 0, -4);
				$langs[] = $lang;
			}
		}
	}
	
	return $langs;
}


/**
 * Uses for valid create HTML tag IMG
 * and fill into him correctly url.
 * When you use this function you
 * mustn't wory obout Fapos install
 * into subdir or SUBDIRS.
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
	return '<img  ' . $additional . 'src="' . get_url($url, $notRoot, false) . '" />';
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
function get_url($url, $notRoot = false, $useLang = true) 
{
    $obj = new AtmUrl;
    return $obj($url, $notRoot, $useLang);
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
 *
 * @param $url (string)
 * @param $header (int)
 *     302 - Moved temporarily
 *     301 - Moved permanently
 */
function redirect($url, $header = 302) {
	
	$allowed_headers = array(301, 302);
	if (!in_array($header, $allowed_headers)) $header = 301;

	if (isset($_GET['ajax'])) die(json_encode(array('redirect' => $url)));
	
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



function CheckUserOnline($user_id) {
	$users = getOnlineUsers();
	return array_key_exists($user_id, $users);
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
	
	return (!empty($users)) ? $users : array();
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
function getMicroTime($base_time = false) {
    list($usec, $sec) = explode(" ", microtime());
    $microtime = ((float)$usec + (float)$sec);
    if (!empty($base_time)) {
        return round($microtime - $base_time, 7);
    }
    return $microtime;
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



/**
 * Similar to copy
 * @Recursive
 */
function copyr($source, $dest, $perms = 0555)
{
    // Simple copy for a file
    if (is_file($source)) {
        copy($source, $dest);
        @chmod($dest, $perms);
        return true;
    }
 
    // Make destination directory
    if (!is_dir($dest)) {
        mkdir($dest);
        @chmod($dest, $perms);
    }
   
    // If the source is a symlink
    if (is_link($source)) {
        $link_dest = readlink($source);
        return symlink($link_dest, $dest);
    }
 
    // Loop through the folder
    $dir = dir($source);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }
 
        // Deep copy directories
        if ($dest !== "$source/$entry") {
            copyr("$source/$entry", "$dest/$entry", $perms);
        }
    }
 
    // Clean up
    $dir->close();
    return true;
}



/**
 * Find all files in directory
 * @Recursive
 */
function getDirFiles($path)
{   
	$ret = array();
	$dir_iterator = new RecursiveDirectoryIterator($path);
	$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
	
	foreach ($iterator as $file) {
		if($file->isFile()) $ret[] = str_replace($path, '', (string)$file);
	}
	
	return $ret;
}


function memoryUsage($base_memory_usage) {
    printf("Bytes diff: %s<br />\n", getSimpleFileSize(memory_get_usage() - $base_memory_usage));
}