<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.1                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    AtmApiService class           |
| @copyright     ©Andrey Brykin 2010-2014      |
| @last mod.     2014/12/08                    |
|----------------------------------------------|
|											   |
| any partial or not partial extension         |
| CMS AtomX,without the consent of the         |
| author, is illegal                           |
|----------------------------------------------|
| Любое распространение                        |
| CMS AtomX или ее частей,                     |
| без согласия автора, является не законным    |
\---------------------------------------------*/



/**
 * @version       1.1.0
 * @author        Andrey Brykin
 * @url           http://atomx.net
 */
class AtmApiService {

    private static $apiUrl = 'http://home.atomx.net/';
	

	public static function getLastVersion()
    {
		$id = 'versions.txt';
		$ids = self::$apiUrl . 'cdn/' . $id;
		$cache = new Cache;
		$cache->dirLevel = 0;
		//$cache->lifeTime = 20;
		$cache->cacheDir = ROOT . '/sys/tmp/cdn/';
		
	
		if ($cache->check($id)) { 
			return $cache->read($id);
		}
	
		
		$context  = stream_context_create(array('http' => array('method'  => 'GET', 'timeout' => 2)));
		$new_ver = @file_get_contents($ids, false, $context);
		
		
		if (trim($new_ver) <= FPS_VERSION) {
			$new_ver = true;
		}

		
		preg_match('#(\d{1,2})(.?\d{1,2})(.?\d{0,2})([a-zA-Z. +-_-]{0,30})#i', $new_ver, $matches);
		if (empty($matches)) $matches = array(0);
		if ($matches[0] == true) {
			$matches = $matches[0]; 
		} else {
			$matches = '';
		}
	
		
		$cache->write($matches, $id);
		
		return $matches;
	}

	
    public static function getServerMessage()
    {
        if (!Config::read('allow_server_notifications'))
            return '';

        $context  = stream_context_create(array('http' => array('method'  => 'GET', 'timeout' => 2)));
        $message = @file_get_contents(self::$apiUrl . 'cdn/message.php?v=' . FPS_VERSION, null, $context);
        return (!empty($message))
            ? json_decode($message, true) : array();
    }
}

