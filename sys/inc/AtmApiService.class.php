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

    private static $apiUrl = 'http://atomx.net/';

	public static function getLastVersion()
    {
		$id = 'versions.txt';
		$cache = new Cache;
		$cache->dirLevel = 0;
		$cache->cacheDir = ROOT . '/cdn/';

		
		if ($cache->check($id)) {
			return $cache->read($id);
		}
	
		
		$context  = stream_context_create(array('http' => array('method'  => 'GET', 'timeout' => 2)));
		$new_ver = @file_get_contents(self::$apiUrl . 'cdn/versions.txt', false, $context);	
		if ($new_ver > 46) {
			return false;
		}
		

		$new_ver = preg_replace('#^(\d{1,2})([.]\d{1,2})([.]\d{0,2})(\w{0,40})*$#ui', "$1$2$3$4", $new_ver);
		if ($new_ver <= FPS_VERSION) {
			$new_ver = '';
		}
		
		$cache->write($new_ver, $id);
		
		return $new_ver;
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

