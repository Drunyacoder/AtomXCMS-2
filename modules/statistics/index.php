<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.6.0                         |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    Statistic Module              |
| @copyright     ©Andrey Brykin 2010-2012      |
| @last mod      2012/06/04                    |
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



Class StatisticsModule 
{

	/**
	* @module module indentifier
	*/
	var $module = 'statistics';
	
	
	
	public static function index() 
	{
		$Register = Register::getInstance();
	
	
		//ip
		if (!empty($_SERVER['REMOTE_ADDR'])) {
			$ip = $_SERVER['REMOTE_ADDR'];
		} else {
			$ip = (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : '00.00.00.00';
		}
		if (mb_strlen($ip) > 20 || !preg_match('#^\d+\.\d+\.\d+\.\d+$#', $ip)) $ip = '00.00.00.00'; 
		
		
		if (!file_exists(ROOT . '/sys/logs/counter_ips' . date("Y-m-d"))) {
			$counter_tmp_dirs = glob(ROOT . '/sys/logs/counter_ips*');
			if (!empty($counter_tmp_dirs) && is_array($counter_tmp_dirs)) {
				foreach ($counter_tmp_dirs as $dir) _unlink($dir);
			}
			mkdir(ROOT . '/sys/logs/counter_ips' . date("Y-m-d"), 0777, true);
		}
		
		
		if (!file_exists(ROOT . '/sys/logs/counter_ips' . date("Y-m-d") . '/' . $ip . '.dat')) {
			$inc_ip = 1;
			$file = fopen(ROOT . '/sys/logs/counter_ips' . date("Y-m-d") . '/' . $ip . '.dat', 'w');
			fclose($file);
		} else {
			$inc_ip = 0;
		}
		
		
		//visits from other sites
		if (!empty($_SERVER['HTTP_REFERER']) 
		&& !preg_match('#^http://' . $_SERVER['SERVER_NAME'] . '#', $_SERVER['HTTP_REFERER'])) {
			$other_site_view = 1;
		} else {
			$other_site_view = 0;
		}
		
		//user agent and bot identification
		$other_bot  = 0;
		$yandex_bot = 0;
		$google_bot = 0;
		if (empty($_SERVER["HTTP_USER_AGENT"])) {
			$other_bot = 1;
		} else {
			if (strstr($_SERVER["HTTP_USER_AGENT"], "Yandex")) $yandex_bot = 1;
			elseif (strstr($_SERVER["HTTP_USER_AGENT"], "Googlebot")) $google_bot = 1;
			else {
				if (strstr($_SERVER["HTTP_USER_AGENT"], "StackRambler")
				|| strstr($_SERVER["HTTP_USER_AGENT"], "Scooter")
				|| strstr($_SERVER["HTTP_USER_AGENT"], "Fast")
				|| strstr($_SERVER["HTTP_USER_AGENT"], "infoseek")
				|| strstr($_SERVER["HTTP_USER_AGENT"], "YahooBot")
				|| strstr($_SERVER["HTTP_USER_AGENT"], "aport")
				|| strstr($_SERVER["HTTP_USER_AGENT"], "slurp")
				|| strstr($_SERVER["HTTP_USER_AGENT"], "architextspider")
				|| strstr($_SERVER["HTTP_USER_AGENT"], "lycos")
				|| strstr($_SERVER["HTTP_USER_AGENT"], "grabber"))
				$other_bot = 1;
			}
		}
		
		//referer
		$referer = (!empty($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
		
		//check coocie
		if (isset($_COOKIE['counter'])) {
			$cookie = 0;
		} else {
			$cookie = 1;
			//cookie die in 23:59:59 this day
			$curdate = date("n,j,Y");
			$curdate = explode(',', $curdate);
			$timestamp = mktime(23, 59, 59, $curdate[0], $curdate[1], $curdate[2]);
			setcookie( 'counter', md5($ip), $timestamp, '/' );
		}
		
		
		
		touchDir(ROOT . '/sys/logs/counter/', 0777);
		$tmp_datafile = ROOT . '/sys/logs/counter/' . date("Y-m-d") . '.dat';
		if (file_exists($tmp_datafile) && is_readable($tmp_datafile)) {
			$stats = unserialize(file_get_contents($tmp_datafile));
			
			$stats['views'] 			= $stats['views'] + 1;
			$stats['cookie']			= $stats['cookie'] + $cookie;
			$stats['ips'] 				= $stats['ips'] + $inc_ip;
			$stats['yandex_bot_views'] 	= $stats['yandex_bot_views'] + $yandex_bot;
			$stats['google_bot_views'] 	= $stats['google_bot_views'] + $google_bot;
			$stats['other_bot_views'] 	= $stats['other_bot_views'] + $other_bot;
			$stats['other_site_visits'] = $stats['other_site_visits'] + $other_site_view;

		} else {
			$stats = array(
				'views' => 1,
				'cookie' => 1,
				'ips' => 1,
				'yandex_bot_views' => $yandex_bot,
				'google_bot_views' => $google_bot,
				'other_bot_views' => $other_bot,
				'other_site_visits' => $other_site_view,
			);
		}

		$f = fopen($tmp_datafile, 'w+'); flock($f, LOCK_EX);
		fwrite($f, serialize($stats));
		flock($f, LOCK_UN); fclose($f);
		
		
		//statistics data for counter image
		if (!file_exists(ROOT . '/sys/logs/overal_stats.dat')) {
			StatisticsModule::_updateOveralHits();
		}
		$overal = unserialize(file_get_contents(ROOT . '/sys/logs/overal_stats.dat'));
		if (!isset($overal['hits'])) {
			$overal['hits'] = StatisticsModule::_updateOveralHits();
		}
		$all_hits = ((int)$overal['hits'] + $stats['views']);
		$hosts = $stats['cookie'];
		$hits = $stats['views'];
		
		
		//write into data base and delete file (one time in day)
		$tmp_files = glob(ROOT . '/sys/logs/counter/*.dat');
		if (!empty($tmp_files) && count($tmp_files) > 1) {
			foreach ($tmp_files as $file) {
				$date = substr(strrchr($file, '/'), 1, 10);
				if ($date == date("Y-m-d")) continue;
				StatisticsModule::_writeIntoDataBase($date);
				unlink($file);
				StatisticsModule::_deleteOveralKey('hits');
			}
		}
		
	
		$im     = imagecreatefrompng(ROOT . "/sys/img/statistics.png");
		$orange = imagecolorallocate($im, 20, 10, 20);
		$image_x = imagesx($im);
		imagestring($im, 1, $image_x - (strlen($all_hits) * 5), 3, $all_hits, $orange);
		imagestring($im, 1, $image_x - (strlen($hits) * 5), 14, $hits, $orange);
		imagestring($im, 1, $image_x - (strlen($hosts) * 5), 24, $hosts, $orange);
		imagepng($im, ROOT . '/sys/img/counter.png');
		imagedestroy($im);
		
		
		
		//who online
		touchDir(ROOT . '/sys/logs/counter_online/');
		$path = ROOT . '/sys/logs/counter_online/online.dat';
		$users = array();
		$guests = array();
		$online_users = array();
		if (file_exists($path) && is_readable($path)) {
			$data = unserialize(file_get_contents($path));
			$users = (!empty($data['users'])) ? $data['users'] : array();
			$guests = (!empty($data['guests'])) ? $data['guests'] : array();
		}
		foreach ($users as $key => $user) {
			if ($user['expire'] < time()) {
				unset($users[$key]);
				break;
			}
			
			
			// online users list
			if (strstr($key, 'bot')) {
				$online_users[] = '<span class="botname">' . h($user['name']) . '</span>';
				continue;
			}
			$color = '';
			if (isset($user['status'])) {
				$group_info = $Register['ACL']->get_user_group($user['status']);
				if (!empty($group_info['color'])) $color = 'color:#' . $group_info['color'] . ';';
			}
			$online_users[] = get_link(h($user['name']), '/users/info/' . $key, array('style' => $color));
		}
		
		
		foreach ($guests as $key => $guest) {
			if ($guest['expire'] < time()) unset($guests[$key]);
		}
		$_SESSION['online_users_list'] = (count($online_users)) ? implode(', ', $online_users) : '';

		
		// Max users online in one time
		$all_online = intval(count($users) + count($guests));
		if (!empty($overal['max_users_online']) && is_numeric($overal['max_users_online'])) {
			if ($overal['max_users_online'] < $all_online) {
				StatisticsModule::_updateOveralHits(array(
					'max_users_online',
					'max_users_online_date',
				), array(
					$all_online,
					date("Y-m-d"),
				));
			}
		} else {
			StatisticsModule::_updateOveralHits(array(
				'max_users_online',
				'max_users_online_date',
			), array(
				$all_online,
				date("Y-m-d"),
			));
		}
		
		
		if (empty($_SERVER['HTTP_USER_AGENT'])) $_SERVER["HTTP_USER_AGENT"] = '';
		if (!empty($_SESSION['user']['id'])) {
			$users[$_SESSION['user']['id']] = array(
				'expire' => time() + ($Register['Config']->read('time_on_line') * 60),
				'name' => $_SESSION['user']['name'],
				'status' => $_SESSION['user']['status'],
			);
		} else if (strstr($_SERVER["HTTP_USER_AGENT"], "StackRambler")) {
			$users['bot_rambler'] = array(
				'expire' => time() + ($Register['Config']->read('time_on_line') * 60),
				'name' => 'Rambler[bot]',
			);
		} else if (strstr($_SERVER["HTTP_USER_AGENT"], "YahooBot")) {
			$users['bot_yahoo'] = array(
				'expire' => time() + ($Register['Config']->read('time_on_line') * 60),
				'name' => 'Yahoo[bot]',
			);
		} else if (strstr($_SERVER["HTTP_USER_AGENT"], "Yandex")) {
			$users['bot_yandex'] = array(
				'expire' => time() + ($Register['Config']->read('time_on_line') * 60),
				'name' => 'Yandex[bot]',
			);
		} else if (strstr($_SERVER["HTTP_USER_AGENT"], "Googlebot")) {
			$users['bot_google'] = array(
				'expire' => time() + ($Register['Config']->read('time_on_line') * 60),
				'name' => 'Google[bot]',
			);
		} else {
			$guests[$ip] = array(
				'expire' => time() + ($Register['Config']->read('time_on_line') * 60),
			);
		}
		file_put_contents($path, serialize(array('users' => $users, 'guests' => $guests)));
		return;
	}
	
	
	
	/**
	* @param (string) $key - key for delete
	*
	* clean overal stats key
	*/
	private function _deleteOveralKey($key) {
		$data = unserialize(file_get_contents(ROOT . '/sys/logs/overal_stats.dat'));
		if (array_key_exists($key, $data)) unset($data[$key]);
		file_put_contents(ROOT . '/sys/logs/overal_stats.dat', serialize($data));
	}
	
	
	
	/**
	* update overal all hits value
	*/
	public static function _updateOveralHits($keys = array(), $values = array()) {
		clearstatcache();
		$overal_file = ROOT . '/sys/logs/overal_stats.dat';
		
		$Register = Register::getInstance();
		$Model = $Register['ModManager']->getModelInstance('Statistics');
		
		$res = $Model->getCollection(array(), array(
			'fields' => array('SUM(`views`) as all_hits'),
		));
		$all_hits = (!empty($res)) ? $res[0]->getAll_hits() : 0;
		
		
		if (file_exists($overal_file)) {
			$data = unserialize(file_get_contents($overal_file));
		} else {
			$data = array();
		}
		$data['hits'] = $all_hits;
		
		
		if (count($keys) > 0 && count($values) > 0) {
			foreach ($keys as $k => $key) {
				$data[$key] = $values[$k];
			}
		}
		
		
		file_put_contents($overal_file, serialize($data));
		
		return $all_hits;
	}
	
	
	
	/**
	* write into database
	*/
	function _writeIntoDataBase($date) {
		$file = ROOT . '/sys/logs/counter/' . $date . '.dat';
		if (!preg_match('#^\d{4}-\d{2}-\d{2}$#', $date) || !file_exists($file)) return;
		
		
		$Register = Register::getInstance();
		$Model = $Register['ModManager']->getModelInstance('Statistics');
		
		
		$res = $Model->getCollection(array('date' => $date));
		if (count($res) > 0) { 
			return;
		} else {
			$stats = unserialize(file_get_contents($file));
			$data = array(
				'ips' => (int)$stats['ips'],
				'cookie' => (int)$stats['cookie'],
				'date' => $date,
				'views' => (int)$stats['views'],
				'yandex_bot_views' => (int)$stats['yandex_bot_views'],
				'google_bot_views' => (int)$stats['google_bot_views'],
				'other_bot_views' => (int)$stats['other_bot_views'],
				'other_site_visits' => (int)$stats['other_site_visits'],
			);
			$statisticEntity = new StatisticsEntity($data);
			$statisticEntity->save();
		}
	}

	
	/**
	* if statistics OFF
	*/
	function viewOffCounter() {
		copy(ROOT . '/sys/img/counter_off.png', ROOT . '/sys/img/counter.png');
	}
	
	
	/**
	* view counter
	*/
	function viewCounter() {
		$_hosts = $this->Model->getCollection(array("`date` >= '" . date("Y-m-d") . "'"));
		$hosts = (!empty($_hosts[0])) ? $_hosts[0]['ips'] : 0;
		$hits = (!empty($_hosts[0])) ? $_hosts[0]['views'] : 0;
	
		header("Content-type: image/png");
	
		$im     = imagecreatefrompng(ROOT . "/sys/img/statistics.png");
		$orange = imagecolorallocate($im, 20, 10, 20);
		$px     = (imagesx($im) - 17);
		imagestring($im, 1, $px, 1, $hits, $orange);
		imagestring($im, 1, $px, 13, $hits, $orange);
		imagestring($im, 1, $px, 22, $hosts, $orange);
		imagepng($im);
		imagedestroy($im);
	}
	

	/*
	* return who online
	* also we have analog in helpers lib
	*/
	function getWhoOnline() {
		$path = ROOT . '/sys/logs/counter_online/online.dat';
		$users = 0;
		$quests = 0;
		if (file_exists($path) && is_readable($path)) {
			$data = unserialize(file_get_contents($path));
			$users = (isset($data['users'])) ? count($data['users']) : 0;
			$guests = (isset($data['guests'])) ? count($data['guests']) : 0;
		}
		return array('users' => $users, 'quests' => $quests);
	}
	


}

?>