<?php
/*-----------------------------------------------\
| 												 |
|  @Author:       Andrey Brykin (Drunya)         |
|  @Email:        drunyacoder@gmail.com          |
|  @Site:         http://fapos.net			     |
|  @Version:      1.0.1                          |
|  @Project:      CMS                            |
|  @Package       CMS Fapos                      |
|  @Subpackege    Plugins Class                  |
|  @Copyright     ©Andrey Brykin 2010-2013       |
|  @Last mod.     2013/01/17                     |
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

class Plugins {


	/**
	 * Find plugin by key and launch his
	 *
	 * @param string $key
	 * @param mixed $params
	 */
	public static function intercept($key, $params = array()) {
		$plugins = glob(ROOT . '/sys/plugins/' . $key . '*');
	
		if (count($plugins) > 0 && is_array($plugins)) {
			foreach ($plugins as $plugin) {
				if (!is_dir($plugin)) continue;
				
				$pl_conf = file_get_contents($plugin . '/config.dat');
				$pl_conf = unserialize($pl_conf);
				if (empty($pl_conf['active'])) continue;
				
				
				include_once $plugin . '/index.php';
				
				$pl_obj = new $pl_conf['className']($params);
				$params = $pl_obj->common($params);
			}
		}
		
		return $params;
	}
}
