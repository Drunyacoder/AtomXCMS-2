<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    AtmApiService class           |
| @copyright     ©Andrey Brykin 2010-2014      |
| @last mod.     2014/11/01                    |
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
 * @version       1.0.0
 * @author        Andrey Brykin
 * @url           http://atomx.net
 */
class AtmApiService {

    private static $apiUrl = 'http://home.develdo.com/';

	public static function getLastVersion()
    {
        $context  = stream_context_create(array('http' => array('method'  => 'GET', 'timeout' => 2)));
        $new_ver = @file_get_contents(self::$apiUrl . 'cdn/versions.txt', null, $context);
        return (!empty($new_ver) && $new_ver > FPS_VERSION)
            ? $new_ver : false;
	}


    public static function getServerMessage()
    {
        if (!Config::read('allow_server_messages'))
            return '';

        $context  = stream_context_create(array('http' => array('method'  => 'GET', 'timeout' => 2)));
        $message = @file_get_contents(self::$apiUrl . 'cdn/message.php?v=' . FPS_VERSION, null, $context);
        return (!empty($message))
            ? json_decode($message, true) : array();
    }
}

