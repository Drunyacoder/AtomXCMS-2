<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0.12                        |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    Validate class                |
| @copyright     ©Andrey Brykin 2010-2011      |
| @last mod      2011/12/21                    |
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


/*
 * REGEX for titles. 
 * Allowed chars. You can change this.
 */
//define ('V_TITLE', '#^[A-ZА-Яа-яa-z0-9\s-\(\),\._\?\!\w\d\{\} ]+$#ui');
define ('V_TITLE', '#^[A-ZА-Яа-яa-z0-9ё\s\-(),._\?!\w\d\{\}\<\>:=\+&%\$\[\]\\\/"\']+$#ui');


define ('V_INT', '#^\d+$#i');
define ('V_TEXT', '#^[\wA-ZА-Яа-яa-z0-9\s\-\(\):;\[\]\+!\.,&\?/\{\}="\']*$#uim');
define ('V_MAIL', '#^[0-9a-z_\-\.]+@[0-9a-z\-\.]+\.[a-z]{2,6}$#i');
define ('V_URL', '#^((https?|ftp):\/\/)?(www.)?([0-9a-z]+(-?[0-9a-z]+)*\.)+[a-z]{2,6}\/?([-0-9a-z_]*\/?)*([-0-9A-Za-zА-Яа-я_]+\.?[-0-9a-z_]+\/?)*$#i');
define ('V_CAPTCHA', '#^[\dabcdefghijklmnopqrstuvwxyz]+$#i');
define ('V_LOGIN', '#^[- _0-9a-zА-Яа-я@]+$#ui');

class Validate {



	public static function getCurrentInputsValues($entity, $pattern = array())
    {
        if (!empty($_SESSION['viewMessage'])) {
			$session = $_SESSION['viewMessage'];
        } else if (!empty($_SESSION['FpsForm'])) {
            $session = $_SESSION['FpsForm'];
        }
		
		
		if (empty($pattern) && is_array($entity)) $pattern = $entity;
		foreach ($pattern as $key => $value) {
		
			if (is_object($entity)) {
				$getter = 'get' . ucfirst($key);
				$setter = 'set' . ucfirst($key);
				if (!empty($session[$key])) {
					$entity->$setter($session[$key]);
				} else if (!$entity->$getter()) {
					$entity->$setter($value);
				}
			} else if (is_array($entity)) {
				if (!empty($session[$key])) {
					$entity[$key] = $session[$key];
				} else if (!isset($entity[$key])) {
					$entity[$key] = $value;
				}
			}
		}
		return $entity;
    }


	public function cha_val ($data, $filter = '#^\w*$#Uim') {
		if (!preg_match($filter, $data)) return false;
		else return true;
	}
	
	
	public function len_val ($data, $min = 1, $max = 100) {
	
		if (mb_strlen($data) > $max) return 'Текст слишком велик';
		elseif (mb_strlen($data) < $min) return 'Текст слишком короткий';
		
		return true;
	}
	
	
	//find similar records from  $sourse (< array(table, field) >)
	// TODO || Delete
	public function uniq_val($data, $sourse, $type = 'low') {
		global $FpsDB;
		if ($type == 'hight') {
			//array with russian letters
			$rus = array( "А","а","В","Е","е","К","М","Н","О","о","Р","р","С","с","Т","Х","х" );
			// array with latinic letters
			$eng = array( "A","a","B","E","e","K","M","H","O","o","P","p","C","c","T","X","x" );
			// change russian to latinic
			$eng_new_data = str_replace($rus, $eng, $data);
			// change latinic to russian
			$rus_new_data = str_replace($eng, $rus, $data);
			
			// create SQL query
			$sql = "SELECT * FROM `{$sourse['table']}`
					WHERE `{$sourse['field']}` LIKE '".mysql_real_escape_string( $data )."' OR
					`{$sourse['field']}` LIKE '".mysql_real_escape_string( $eng_new_data )."' OR
					`{$sourse['field']}` LIKE '".mysql_real_escape_string( $rus_new_data )."'";
		  
		} else { //security level not hight...
		
			$sql = "SELECT COUNT(*) 
					FROM `{$sourse['table']}`  
					WHERE `{$sourse['field']}` LIKE '" . mysql_real_escape_string($data) . "'";
					
		}
		$query = $FpsDB->query($sql);

		return (count($query) > 0) ? false : true;
	}
	
}

?>