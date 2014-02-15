<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    AtmDateTime class             |
| @copyright     ©Andrey Brykin 2010-2014      |
| @last mod.     2014/02/15                    |
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
 * @version       1.0.0
 * @author        Andrey Brykin
 * @url           http://fapos.net
 */
class AtmDateTime {

	public static function getDate($date, $format = 'Y-m-d H:i:s'){
		$user_timezone = (!empty($_SESSION['user']) && !empty($_SESSION['user']['timezone']))
			? $_SESSION['user']['timezone']
			: '+00';
		
		$dateObj = new DateTime($date);
		$dateObj->modify($user_timezone . ' hour');
		return $dateObj->format($format);
	}
}

