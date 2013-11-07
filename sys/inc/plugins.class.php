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
	
	public static $map = array();


	public function __construct() {
		$dirs = glob(ROOT . '/sys/plugins/*');
		
		
		if (!empty($dirs)) {
			foreach ($dirs as $dir) {
				
				
				if (file_exists($dir . '/config.dat')) {
					$config = json_decode(file_get_contents($dir . '/config.dat'), true);
					if (!empty($config['points'])) {

					
						if (is_string($config['points'])) {
						
							if (empty(self::$map[$config['points']])) self::$map[$config['points']] = array();
							self::$map[$config['points']][] = $dir;
							
							
						} else if (is_array($config['points'])) {
							foreach ($config['points'] as $point) {
							
								if (empty(self::$map[$point])) self::$map[$point] = array();
								self::$map[$point][] = $dir;
							}
						}
					}
				}
			}
		}
	}

	
	
	/**
	 * Find plugin by key and launch his
	 *
	 * @param string $key
	 * @param mixed $params
	 */
	public static function intercept($key, $params = array()) {

		if (!empty(self::$map[$key])) {
			foreach (self::$map[$key] as $plugin) {
				
				$pl_conf = file_get_contents($plugin . '/config.dat');
				$pl_conf = json_decode($pl_conf, true);
				if (empty($pl_conf['active'])) continue;
				
				
				include_once $plugin . '/index.php';
				
				$pl_obj = new $pl_conf['className']($params);
				$params = $pl_obj->common($params);
			}
		}
		
		return $params;
	}
}
