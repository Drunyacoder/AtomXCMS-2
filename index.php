<?php
/*-----------------------------------------------\
| 												 |
|  @Author:       Andrey Brykin (Drunya)         |
|  @Version:      1.2.9                          |
|  @Project:      CMS                            |
|  @package       CMS Fapos                      |
|  @subpackege    Entry dot                      |
|  @copyright     ©Andrey Brykin 2010-2012       |
|  @last mod.     2012/04/29                     |
\-----------------------------------------------*/

/*-----------------------------------------------\
| 												 |
|  any partial or not partial extension          |
|  CMS Fapos,without the consent of the          |
|  author, is illegal                            |
|------------------------------------------------|
|  Любое распространение                         |
|  CMS Fapos или ее частей,                      |
|  без согласия автора, является не законным     |
\-----------------------------------------------*/




header('Content-Type: text/html; charset=utf-8');







if (file_exists('install')) {
	if (file_exists('sys/settings/config.php')) {
		include_once ('sys/settings/config.php');
		if (!empty($set)) {
			if (!empty($set['db']['name'])) {
				echo 'Before use your site, delete INSTALL dir! <br />Перед использованием удалите папку INSTALL'; die();
			}
		}
	}
	header('Location: install'); die();
}




include_once 'sys/boot.php';

Plugins::intercept('before_pather', array());

/**
 * Parser URL
 * Get params from URL and launch needed module and action
 */
new Pather($Register);
Plugins::intercept('after_pather', array());
//pr($Register);
