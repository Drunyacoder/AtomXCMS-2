<?php
/*-----------------------------------------------\
| 												 |
|  @Author:       Andrey Brykin (Drunya)         |
|  @Email:        drunyacoder@gmail.com          |
|  @Site:         http://atomx.net			     |
|  @Version:      1.1.0                          |
|  @Project:      CMS                            |
|  @Package       CMS Fapos                      |
|  @Subpackege    Plugins Class                  |
|  @Copyright     ©Andrey Brykin                 |
|  @Last mod.     2014/03/31                     |
|------------------------------------------------|
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
	
	private $errors = array();
	
	private $files = array();


	public function __construct() {
		$dirs = glob(ROOT . '/sys/plugins/*', GLOB_ONLYDIR);
		
		
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
	
	
	
	public function getErrors() {
		return $this->errors;
	}
	
	
	
	public function getFiles() {
		return $this->files;
	}
	
	
	
	public function install($filename) {
		$src = ROOT . '/sys/tmp/' . $filename;
		$dest = ROOT . '/sys/tmp/install_plugin/';
	
		
		Zip::extractZip($src, $dest);
		if (!file_exists($dest)) {
			$this->errors = __('Some error occurred');
			return false;
		}
		
		$tmp_plugin_path = glob($dest . '*', GLOB_ONLYDIR);
		$tmp_plugin_path = $tmp_plugin_path[0];
		$plugin_basename = substr(strrchr($tmp_plugin_path, '/'), 1);
		$plugin_path = ROOT . '/sys/plugins/' . $plugin_basename;
			
		
		copyr($dest, ROOT . '/sys/plugins/');
		$this->files = getDirFiles($plugin_path);
		
		
		if (file_exists($plugin_path . '/config.dat')) {
			$config = json_decode(file_get_contents($plugin_path . '/config.dat'), true);
			
			
			include_once $plugin_path . '/index.php';
			$className = $config['className'];
			$obj = new $className(null);
			
			if (method_exists($obj, 'install')) {
				$obj->install();
			}
		}
		
		_unlink($src);
		_unlink($dest);
		
		return true;
	}
	
	
	
	public function foreignUpload($url) {
		$headers = get_headers($url,1);
		if (!empty($headers['Content-Disposition']))
			preg_match('#filename="(.*)"#iU', $headers['Content-Disposition'], $matches);
		$filename = (isset($matches[1])) ? $matches[1] : basename($url);
		
		
		if (copy($url, ROOT . '/sys/tmp/'.$filename)) {
			return $filename;
			
		} else {
			$this->errors = __('Some error occurred');
			return false;
		}
	}
	
	
	
	public function localUpload($field) {
		if (empty($_FILES[$field]['name'])) {
			$this->errors = __('File not found');
			return false;
		}
		
		$ext = strrchr($_FILES[$field]['name'], '.');
		if (strtolower($ext) !== '.zip') {
			$this->errors = __('Wrong file format');
			return false;
		}
		
		$filename = $_FILES[$field]['name'];
		
		
		if (move_uploaded_file($_FILES[$field]['tmp_name'], ROOT . '/sys/tmp/'.$filename)) {
			return $filename;
			
		} else {
			$this->errors = __('Some error occurred');
			return false;
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
				$params = $pl_obj->common($params, $key);
			}
		}
		
		return $params;
	}
}
